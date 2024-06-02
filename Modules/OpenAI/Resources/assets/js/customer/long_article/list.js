'use strict';

$('.article-modal-toggle').on("click", function (e) {
    e.preventDefault();
    $('.delete-article').attr('data-id', $(this).attr('id'));
    $('.index-modal').toggleClass('is-visible');
});


$('.delete-article').on('click', function () {
    var longArticleId = $(this).attr("data-id");
    doAjaxprocess(
        SITE_URL + "/user/articles/" + longArticleId,
        {
            _token: CSRF_TOKEN,
            _method: 'DELETE',
            longArticleId : longArticleId,
        },
        'post',
        'json'
    ).done(function(data) {
        toastMixin.fire({
            title: data.message,
            icon: data.status
        });
        $('#longArticle_'+longArticleId).remove();
        
        // If the row is last row and it's deleted, No data found page display
        if ($('.longArtileTable tr').length < 2) {
            
            $('.longArticleTableHeader').remove();
            $('.longArtileTable tbody').html(
                `<tr class="empty-row">
                    <td colspan="6">
                        <svg class="mx-auto mt-10" xmlns="http://www.w3.org/2000/svg" width="44" height="44" viewBox="0 0 44 44" fill="none">
                            <g clip-path="url(#clip0_2698_2638)">
                            <path d="M38.6467 13.4583H5.35361C4.07374 13.4583 3.03613 14.4958 3.03613 15.7757V30.9319H40.9641V15.7757C40.9641 14.4959 39.9265 13.4583 38.6467 13.4583Z" fill="#FF9A00"/>
                            <path d="M8.8972 0C7.26421 0 5.94043 1.32378 5.94043 2.95677V41.0432C5.94043 42.6762 7.26421 44 8.8972 44H35.1026C36.7356 44 38.0594 42.6762 38.0594 41.0432V9.11745C38.0594 8.52337 37.8234 7.95369 37.4034 7.53354L30.5258 0.655961C30.1057 0.235984 29.5359 0 28.9419 0L8.8972 0Z" fill="#F5F5F5"/>
                            <path d="M37.4035 7.53367L32.8447 2.97485L32.8284 10.622C32.8274 11.102 33.2163 11.4918 33.6963 11.4918C34.1757 11.4918 34.5642 11.8804 34.5642 12.3597V41.0434C34.5642 42.6763 33.2404 44.0001 31.6074 44.0001H35.1027C36.7357 44.0001 38.0594 42.6763 38.0594 41.0434V12.1156V9.11749C38.0596 8.52341 37.8236 7.95373 37.4035 7.53367Z" fill="#EAEAEA"/>
                            <path d="M37.4033 7.5335L30.5257 0.655926C30.3342 0.464457 30.1114 0.311746 29.8696 0.20166V5.23279C29.8696 6.86577 31.1934 8.18955 32.8264 8.18955H37.8575C37.7475 7.94772 37.5948 7.72497 37.4033 7.5335Z" fill="#A8D0D5"/>
                            <path d="M16.3243 21.9575L15.7131 20.5742C15.3422 19.7349 14.511 19.1934 13.5934 19.1934H5.35361C4.07374 19.1934 3.03613 20.2309 3.03613 21.5108V41.6824C3.03613 42.9623 4.07366 43.9999 5.35361 43.9999H38.6467C39.9265 43.9999 40.9641 42.9624 40.9641 41.6824V25.6559C40.9641 24.376 39.9266 23.3384 38.6467 23.3384H18.4441C17.5264 23.3384 16.6952 22.7969 16.3243 21.9575Z" fill="#FFB541"/>
                            <path d="M12.187 20.5742L12.7982 21.9575C13.1691 22.7968 14.0003 23.3383 14.918 23.3383H18.444C17.5263 23.3382 16.6952 22.7968 16.3243 21.9575L15.7131 20.5742C15.3422 19.7349 14.511 19.1934 13.5934 19.1934H10.0674C10.985 19.1934 11.8161 19.7349 12.187 20.5742Z" fill="#FFA812"/>
                            <path d="M38.6468 23.3384H35.1209C36.4006 23.3385 37.4381 24.376 37.4381 25.6559V41.6825C37.4381 42.9624 36.4006 44 35.1206 44H38.6467C39.9266 44 40.9642 42.9625 40.9642 41.6825V25.6558C40.9643 24.3759 39.9267 23.3384 38.6468 23.3384Z" fill="#FFA812"/>
                            <path d="M16.6176 4.86731H10.0499C9.68309 4.86731 9.38574 4.56997 9.38574 4.20319C9.38574 3.83641 9.68309 3.53906 10.0499 3.53906H16.6176C16.9844 3.53906 17.2818 3.83641 17.2818 4.20319C17.2818 4.56997 16.9845 4.86731 16.6176 4.86731Z" fill="#3693BD"/>
                            <path d="M16.6176 8.86121H10.0499C9.68309 8.86121 9.38574 8.56387 9.38574 8.19708C9.38574 7.8303 9.68309 7.53296 10.0499 7.53296H16.6176C16.9844 7.53296 17.2818 7.8303 17.2818 8.19708C17.2818 8.56387 16.9845 8.86121 16.6176 8.86121Z" fill="#3693BD"/>
                            </g>
                            <defs>
                            <clipPath id="clip0_2698_2638">
                            <rect width="44" height="44" fill="white"/>
                            </clipPath>
                            </defs>
                        </svg>
                        <p class="text-color-14 dark:text-white text-center font-medium font-Figtree text-20 mt-6">${noArticleFoundText}</p>
                        <p class="text-center font-medium text-color-89 text-15 px-5 font-Figtree mt-3 md:w-[450px] mx-auto">${articleHelpText}</p>
                        <a
                        class="magic-bg w-max rounded-xl text-16 text-white font-semibold py-3 px-6 mx-auto flex text-center my-10 cursor-pointer" href="${articleGenerateUrl}">
                            <span>${articleGenerateButtonText}</span>
                        </a>
                    </td>
                </tr>`
            );
    
            // If the current generated article deleted, local storage will be reset
            if (longArticleId == JSON.parse(localStorage.getItem('longArticleFormData')).longArticleId) {
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
                
                localStorage.setItem('longArticleFormData', JSON.stringify(initLongArticleFormData));
            }
        }

    });
});