<?php

class NewsController
{
    /**
     * Возвращает список всех новостей
     *
     * (Тут хотел добавить кеширование еще, но для 15 новостей - нет смысла)
     * @return array
     */
    public static function getNews()
    {
        $news = self::getNewsFromDB();

        if (!is_array($news)) {
            echo $news;
            return [];
        }

        foreach ($news as $key => $oneNews) {
            $news[$key]['preview_text'] = (mb_strlen($oneNews['text'], 'utf-8') <= 200)
                ? $oneNews['text']
                : mb_strimwidth($oneNews['text'], 0, 200, "...");
        }

        uasort($news, function ($post1, $post2) {
            return $post2['date'] <=> $post1['date'];
        });

        return $news;
    }

    /**
     * @param string $id
     * @return array
     */
    public static function getNewsById(string $id)
    {
        $arParams = [
            "id" => $id
        ];
        $news = self::getNewsFromDB($arParams);

        if (!is_array($news)) {
            return [];
        }

        return $news;
    }

    /**
     * @param array $arParams
     * @return array|bool|string
     */
    private static function getNewsFromDB($arParams = array())
    {
        $sql = "SELECT * FROM posts";

        if (!empty($arParams)) {
            if (isset($arParams['id'])) {
                $db = DBController::getConnection();
                $id = $db->real_escape_string(htmlspecialchars($arParams['id']));
                $sql .= " WHERE id='{$id}'";
            }
        }

        $result = DBController::Query($sql);

        if ($result === false) {
            return "База данных временно недоступна. Попробуйте позднее";
        } elseif (empty($result)) {
            if (!empty($arParams)) return [];

            $result = self::getNewsFromRbk();
            self::setNewsToDB($result);
            return $result;
        }

        $returnNews = [];
        while ($news = $result->fetch_assoc()) {
            $returnNews[$news['id']] = $news;
        }

        return $returnNews;
    }

    /**
     * @param array $news
     */
    private static function setNewsToDB(array $news)
    {
        $db = DBController::getConnection();
        foreach ($news as $key => $post) {
            $id = $db->real_escape_string($key);
            $base_link = $db->real_escape_string($post['base_link']);
            $title = $db->real_escape_string($post['title']);
            $text = $db->real_escape_string($post['text']);
            $image = $db->real_escape_string($post['image']);
            $date = $db->real_escape_string($post['date']);
            $sql = "INSERT INTO `rbc`.`posts` (`id`, `base_link`, `title`, `text`, `image`, `date`)
                    VALUES ('{$id}', '{$base_link}', '{$title}', '{$text}', '{$image}', '{$date}');";
            DBController::Query($sql);
        }
    }

    /**
     * В рамках данной задачи проще удалить новости из БД и заполнить заново
     *
     * Однако в реальной разработке: надо добавлять новые новости в БД
     * и делать sql выборку по дате первых 15 новостей
     *
     * @return bool
     */
    public static function updateNews()
    {
        $sql = "TRUNCATE TABLE posts";

        $result = DBController::Query($sql);

        return !($result === false);
    }

    /**
     * @return array
     */
    private static function getNewsFromRbk()
    {
        $curTime = time();
        $decodeJson = ParserController::getDecodeJsonContent("https://www.rbc.ru/v10/ajax/get-news-feed/project/rbcnews/lastDate/{$curTime}/limit/44");
        if (empty($decodeJson) || !isset($decodeJson['items'])) return [];

        $news = $decodeJson['items'];

        usort($news, function ($post1, $post2) {
            return $post2['publish_date_t'] <=> $post1['publish_date_t'];
        });

        $links = [];
        foreach ($news as $oneNews) {
            if (preg_match("/href=\"https\:\/\/www\.rbc\.ru.*\"/", $oneNews['html'], $matches)) {
                $link = preg_replace(array('/href=/', '/"/'), "", reset($matches));
                $keyLink = end(explode("/", explode("?", $link)[0]));
                $links[$keyLink] = $link;
            }
            if (count($links) === 15) break;
        }

        $arPosts = [];
        foreach ($links as $key => $link) {
            $decodeJsonPost = ParserController::getDecodeJsonContent("https://www.rbc.ru/v10/ajax/news/slide/{$key}?slide=1");
            if (empty($decodeJsonPost) || !isset($decodeJsonPost['html'])) continue;

            $pq = phpQuery::newDocument($decodeJsonPost['html']);

            $fullText = "";

            $articleTop = $pq->find('.article__text__overview');
            if (!empty(trim($articleTop->text()))) {
                $fullText .= $articleTop->text() . "<br/><br/>";
            }

            $articlesText = $pq->find('.article__text p');
            foreach ($articlesText as $text) {
                $pqText = pq($text);
                $trimText = trim($pqText->text());
                $fullText .= $trimText;
                if (!empty($trimText)) $fullText .= "<br/><br/>";
            }

            $fullText = trim($fullText);

            if (empty($fullText)) continue;

            $title = $pq->find('div.article__header__title')->text();
            $image = $pq->find('.article__main-image__wrap .article__main-image__image')->attr("src");
            $date = $pq->find('.article__header__date')->attr("content");

            $arPosts[$key] = [
                "date" => date("H:i d.m.Y", strtotime($date)),
                "base_link" => $link,
                "title" => $title,
                "text" => $fullText,
                "image" => $image
            ];
        }

        return $arPosts;
    }
}