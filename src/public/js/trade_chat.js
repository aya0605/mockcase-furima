/**
 * メッセージ編集フォームの表示切り替え
 */
function toggleEdit(messageId) {
    const editForm = document.getElementById(`edit-form-${messageId}`);
    if (editForm) {
        editForm.style.display =
            editForm.style.display === "none" ? "block" : "none";
    }
}

/**
 * 評価モーダルを表示する
 */
function openRatingModal() {
    const modal = document.getElementById("rating-modal");
    if (modal) {
        modal.style.display = "block";
    }
}
function closeRatingModal() {
    const modal = document.getElementById("rating-modal");
    if (modal) {
        modal.style.display = "none";
    }
}
    
// ページ読み込み時の処理
document.addEventListener("DOMContentLoaded", function () {
    const modal = document.getElementById("rating-modal");

    const shouldOpenModal = document.body.dataset.showRatingModal === "true";
    if (shouldOpenModal) {
        openRatingModal();
    }

    window.addEventListener("click", function (event) {
        if (event.target === modal) {
            closeRatingModal();
        }
    });
});

