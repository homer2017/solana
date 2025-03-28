// Log để xác nhận file được tải
console.log("script.js loaded");

// Hàm tiện ích (nếu cần thêm logic sau này)
function initApp() {
    console.log("Ứng dụng đã khởi tạo từ script.js");
}

// Gọi hàm khởi tạo khi trang tải xong
document.addEventListener('DOMContentLoaded', function() {
    initApp();
});