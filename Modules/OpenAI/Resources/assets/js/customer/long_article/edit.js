'use strict';

tinymce.init({
    selector: 'textarea#blog-widget-editor',
    statusbar: false,
    menubar:false,
    promotion:false,
    contextmenu:false,
    toolbar: false,
    body_class: "mceBlackBody",
    plugins: [
    'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
    'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
    'insertdatetime', 'media', 'table',
    ],
    toolbar: 'bold italic backcolor | alignleft aligncenter ' + 'alignright alignjustify | bullist numlist outdent indent | ' +'undo redo| blocks forecolor | ' + 'removeformat | ' + 'link image | ' 
});


var initLongArticleFormData = {
    activeStep: 1,
    longArticleId: '',
    
    titles: {
        for: 'titles',
        data: {
            topic: '',
            keywords: '',
            title: '',
            generatedTitles: []
        }
    },
    outlines: {
        for: 'outlines',
        data: {
            title: '',
            keywords: '',
            generatedOutlines: []
        }
    },
    article: {
        for: 'article',
        data: {
            title: '',
            keywords: '',
            outlines: [],
            generatedArticleBlogId: null
        }
    }
};

if (resetFlag) {
    let longArticleFormData = initLongArticleFormData;
    localStorage.setItem('longArticleFormData', JSON.stringify(longArticleFormData));
}

if (longArticleId == JSON.parse(localStorage.getItem('longArticleFormData')).longArticleId) {
    let longArticleFormData = initLongArticleFormData;
    localStorage.setItem('longArticleFormData', JSON.stringify(longArticleFormData));
}

$(document).on("click", ".save-article", function () {

    if(!tinymce.activeEditor.getContent({format : 'text'})) {
        toastMixin.fire({
            title: jsLang('Nothing To Update'),
            icon: "error",
        });
        return true;
    }

    var parts = window.location.href.split("/");
    $('.save-icon').addClass("hidden")
    $(".loader-update").removeClass("hidden");
    $('.save-article').addClass('cursor-not-allowed');
    $('.save-article-text').text(saveButtonUpdateText);
    
    doAjaxprocess(
        SITE_URL + '/user/articles/' + parts[parts.length-2],
        {
            _token: CSRF_TOKEN,
            _method: 'PATCH',
            id: parts[parts.length-2],
            content: tinymce.activeEditor.getContent(),
        },
        'post',
        'json'
    ).done(function(data) {
        $('.save-article').removeClass('cursor-not-allowed');
        $('.loader-update').addClass('hidden');
        $('.save-icon').removeClass("hidden");
        $('.save-article-text').text(saveButtonText);

        toastMixin.fire({
            title: data.message,
            icon: data.status,
        });
    });
});