/* public/js/trade_chat.js */

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

/**
 * モーダルの外側をクリックした時に閉じる
 */
window.onclick = function (event) {
    const modal = document.getElementById("rating-modal");
    if (event.target === modal) {
        modal.style.display = "none";
    }
};

