<?php
$_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__ . "/../");
include_once $_SERVER['DOCUMENT_ROOT'] . "/includes/header.php";

if (!isset($_REQUEST['POST_ID'])) {
    header("Location: /");
    die();
}

$curNews = reset(NewsController::getNewsById($_REQUEST['POST_ID']));
?>

<div class="news_one">
    <?php if (empty($curNews)): ?>
        <h2 class="news_one__error">Не удалось получить новость, попробуйте позднее</h2>
    <?php else: ?>
        <div class="news_one__block">
            <h2 class="news_one__title"><?= $curNews['title'] ?></h2>
            <?php if (!empty($curNews['image'])): ?>
                <img src="<?= $curNews['image'] ?>" alt="" class="news_one__image">
            <?php endif; ?>
            <div class="news_one__text"><?= $curNews['text'] ?></div>
            <a href="<?= $curNews['base_link'] ?>" class="news_one__source">Источник</a>
        </div>
    <?php endif;?>

    <a href="/" class="news_one__back">Вернуться на главную</a>
</div>

<?php
include_once $_SERVER['DOCUMENT_ROOT'] . "/includes/footer.php";