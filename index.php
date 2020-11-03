<?php
$_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__);
include_once $_SERVER['DOCUMENT_ROOT'] . "/includes/header.php";

$allNews = NewsController::getNews();
?>

<div class="news">
    <h1 class="news__header">Новости rbk.ru</h1>
    <a href="javascript:void(0)" class="news__update">Обновить новости</a>
    <?php if (empty($allNews)): ?>
        <p class="news__empty">К сожалению, новостей нет</p>
    <?php else: ?>
        <?php foreach ($allNews as $key => $news): ?>
            <div class="news__block">
                <h2 class="news__title"><?= $news['title'] ?></h2>
                <span class="news__date"><?= $news['date'] ?></span>
                <div class="news__text"><?= $news['preview_text'] ?></div>
                <a href="/news/<?= $key ?>/" class="news__more">Подробнее</a>
            </div>
        <?php endforeach;?>
    <?php endif; ?>
</div>

<?php
include_once $_SERVER['DOCUMENT_ROOT'] . "/includes/footer.php";
