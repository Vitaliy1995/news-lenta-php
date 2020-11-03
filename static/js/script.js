document.addEventListener("DOMContentLoaded", function() {
    let btnUpdate = document.querySelector(".news__update");
    btnUpdate.addEventListener("click", sendUpdateNews);
});

function sendUpdateNews() {
    const xhr = new XMLHttpRequest();

    xhr.open('GET', '/ajax/news_update.php', false);

    xhr.send();

    if (xhr.status === 200) {
        if (xhr.responseText === 'ok') {
            window.location.reload();
            return;
        }
    }
}