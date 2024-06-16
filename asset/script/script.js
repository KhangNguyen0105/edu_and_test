// Mở dropdown khi nhấn vào dấu 3 chấm
document.querySelectorAll('.options i').forEach(icon => {
  icon.addEventListener('click', function(event) {
    event.stopPropagation(); // Ngăn chặn sự kiện nhấp chuột truyền lên
    const dropdownMenu = this.nextElementSibling;
    dropdownMenu.classList.toggle('show');
  });
});

// Đóng menu khi click bên ngoài
window.onclick = function(event) {
  if (!event.target.closest('.options')) {
    document.querySelectorAll('.dropdown-menu').forEach(menu => {
      if (menu.classList.contains('show')) {
        menu.classList.remove('show');
      }
    });
  }
}

// Mở hộp thoại chỉnh sửa khi nhấn nút chỉnh sửa
document.querySelectorAll('.edit').forEach(button => {
  button.addEventListener('click', function() {
    const newsDiv = this.closest('.news');
    const content = newsDiv.querySelector('.news-content p').textContent;
    const discussionId = newsDiv.dataset.discussionId;
    
    document.getElementById('edit-content').value = content;
    document.getElementById('discussion-id').value = discussionId;
    document.getElementById('edit-modal').style.display = 'flex';
  });
});

// Đóng hộp thoại chỉnh sửa khi nhấn dấu X
document.getElementById('close-modal').addEventListener('click', function() {
  document.getElementById('edit-modal').style.display = 'none';
});

// Đóng hộp thoại chỉnh sửa khi nhấn bên ngoài
window.onclick = function(event) {
  const modal = document.getElementById('edit-modal');
  if (event.target == modal) {
    edit_email_modal = 'none';
  }
};

// Gửi yêu cầu xoá bài viết
document.querySelectorAll('.delete').forEach(button => {
  button.addEventListener('click', function() {
    if (confirm("Bạn có chắc chắn muốn xóa bài viết này không?")) {
      const discussionId = this.closest('.news').getAttribute('data-discussion-id');
      const form = document.createElement('form');
      form.method = 'post';
      form.action = '';

      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = 'delete_discussion_id';
      input.value = discussionId;

      form.appendChild(input);
      document.body.appendChild(form);
      form.submit();
    }
  });
});

// Hiển thị modal chỉnh sửa email
document.getElementById('edit-email-btn').addEventListener('click', function() {
  document.getElementById('edit-email-modal').style.display = 'flex';
});

// Đóng modal chỉnh sửa email khi bấm dấu X
document.getElementById('close-modal').addEventListener('click', function() {
  document.getElementById('edit-email-modal').style.display = 'none';
});

// Đóng modal chỉnh sửa email khi bấm bên ngoài
window.addEventListener('click', function(event) {
  if (event.target === document.getElementById('edit-email-modal')) {
    this.document.getElementById('edit-email-modal').style.display = 'none';
  }
});

// Hiển thị modal chỉnh sửa mật khẩu
document.getElementById('change-password-btn').addEventListener('click', function() {
  document.getElementById('change-password-modal').style.display = 'flex';
});

// Đóng modal chỉnh sửa email khi bấm dấu X
document.getElementById('close-change-password-modal').addEventListener('click', function() {
  document.getElementById('change-password-modal').style.display = 'none';
});

// Đóng modal chỉnh sửa email khi bấm bên ngoài
window.addEventListener('click', function(event) {
  if (event.target === document.getElementById('change-password-modal')) {
    this.document.getElementById('change-password-modal').style.display = 'none';
  }
});

// Hiển thị modal chỉnh sửa Họ tên
document.getElementById('edit-fullname-btn').addEventListener('click', function() {
  document.getElementById('edit-fullname-modal').style.display = 'flex';
});

// Đóng modal chỉnh sửa Họ tên khi bấm dấu X
document.getElementById('close-edit-fullname-modal').addEventListener('click', function() {
  document.getElementById('edit-fullname-modal').style.display = 'none';
});

// Đóng modal chỉnh sửa Họ tên khi bấm bên ngoài
window.addEventListener('click', function(event) {
  if (event.target === document.getElementById('edit-fullname-modal')) {
    this.document.getElementById('edit-fullname-modal').style.display = 'none';
  }
});