// scripts.js
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Like button handler
    document.querySelectorAll('.like-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const threadId = this.dataset.threadId;
            fetch('like_thread.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `thread_id=${encodeURIComponent(threadId)}&csrf_token=${encodeURIComponent(csrfToken)}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.like_count !== undefined) {
                    this.nextElementSibling.textContent = data.like_count;
                }
            });
        });
    });

    // Chat polling
    const chatContainer = document.querySelector('.chat-messages');
    if (chatContainer) {
        const params = new URLSearchParams(window.location.search);
        const userId = params.get('user_id');
        if (userId) {
            const poll = () => {
                fetch(`chat.php?user_id=${userId}&poll=1`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.html) {
                            chatContainer.innerHTML = data.html;
                            chatContainer.scrollTop = chatContainer.scrollHeight;
                        }
                    });
            };
            poll();
            setInterval(poll, 3000);
        }
    }
});