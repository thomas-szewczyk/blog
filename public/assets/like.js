function onPostLike(event) {
    event.preventDefault();

    const url = this.href;
    const likeButton = this.querySelector('.postLikeBtn button');
    let likes = this.querySelector('.likes');

    axios.get(url).then(function (response) {
        likes.textContent = response.data.likes;

        if (likeButton.classList.contains('text-danger')) {
            likeButton.classList.replace('text-danger','text-secondary');
        }
        else {
            likeButton.classList.replace('text-secondary', 'text-danger');
        }

    }).catch(function (error) {
        if (error.response.status === 403){
            window.alert("Please sign in to like this post");
        } else {
            window.alert("An error occurred");
        }
    });
}

function onCommentLike(event) {
    event.preventDefault();

    const url = this.href;
    const spanCount = this.querySelector('.commentLikes');
    const likeBtn = this.querySelector('.commentLikeBtn span');

    axios.get(url).then(function (response) {
        spanCount.textContent = response.data.likes;

        if (likeBtn.classList.contains('text-danger')) {
            likeBtn.classList.replace('text-danger','text-secondary');
        }
        else {
            likeBtn.classList.replace('text-secondary', 'text-danger');
        }

    }).catch(function (error) {
        if (error.response.status === 403){
            window.alert("Pleas sign in to like this comment");
        } else {
            window.alert("An error occurred");
        }
    });
}

const likeButton = document.querySelector('.postLikeBtn');
likeButton.addEventListener('click', onPostLike);

const commentLikeButtons = document.querySelectorAll('.commentLikeBtn');
commentLikeButtons.forEach(function (commentLink){
    commentLink.addEventListener('click', onCommentLike);
});
