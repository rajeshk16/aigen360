"use strict";
$(document).ready(function () {
  var $fileInput = $('.file_input');
  var $durationInput = $('#duration');
  var $fileText = $('.file-msg');
  var $loader = $('.upload-loader');
  var $deleteButton = $('#deleteButton');
  var deleteClicked = false;

  function showDeleteButton() {
    $deleteButton.show();
  }
  function hideDeleteButton() {
    $deleteButton.hide();
  }
  $fileInput.on('change', function () {
    var filesCount = this.files.length;
    var self = this;

    if (filesCount === 1) {
      $loader.removeClass('hidden');
      $fileText.addClass('hidden');

      setTimeout(function () {
        var fileName = self.files[0].name;
        var audio = new Audio();
        audio.src = URL.createObjectURL(self.files[0]);

        audio.onloadedmetadata = function () {
          var duration = audio.duration;
          $fileText.text(fileName);
          $durationInput.val(duration.toFixed(2));
          showDeleteButton();
        };
        audio.onerror = function () {
          var duration = audio.duration;
          $fileText.text(fileName);
          $durationInput.val(duration.toFixed(2));
          showDeleteButton();
        };
        $loader.addClass('hidden');
        $fileText.removeClass('hidden');
        deleteClicked = false;
      }, 1000);
    } else {
      $fileText.html('<div class="file-msg justify-center items-center flex gap-2.5 text-color-14 dark:text-white line-clamp-single"><svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M5.99935 0.666016C6.36754 0.666016 6.66602 0.964492 6.66602 1.33268V5.33268H10.666C11.0342 5.33268 11.3327 5.63116 11.3327 5.99935C11.3327 6.36754 11.0342 6.66602 10.666 6.66602H6.66602V10.666C6.66602 11.0342 6.36754 11.3327 5.99935 11.3327C5.63116 11.3327 5.33268 11.0342 5.33268 10.666V6.66602H1.33268C0.964492 6.66602 0.666016 6.36754 0.666016 5.99935C0.666016 5.63116 0.964492 5.33268 1.33268 5.33268H5.33268V1.33268C5.33268 0.964492 5.63116 0.666016 5.99935 0.666016Z" fill="currentColor"/></svg><p>Click or drag audio file here</p></div>');
      $durationInput.val('');
      hideDeleteButton();
    }
  });
  $deleteButton.on('click', function (e) {
    e.preventDefault();
    $fileInput.val(null);
    $fileText.html('<div class="file-msg justify-center items-center flex gap-2.5 text-color-14 dark:text-white line-clamp-single"><svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M5.99935 0.666016C6.36754 0.666016 6.66602 0.964492 6.66602 1.33268V5.33268H10.666C11.0342 5.33268 11.3327 5.63116 11.3327 5.99935C11.3327 6.36754 11.0342 6.66602 10.666 6.66602H6.66602V10.666C6.66602 11.0342 6.36754 11.3327 5.99935 11.3327C5.63116 11.3327 5.33268 11.0342 5.33268 10.666V6.66602H1.33268C0.964492 6.66602 0.666016 6.36754 0.666016 5.99935C0.666016 5.63116 0.964492 5.33268 1.33268 5.33268H5.33268V1.33268C5.33268 0.964492 5.63116 0.666016 5.99935 0.666016Z" fill="currentColor"/></svg><p>Click or drag audio file here</p></div>');
    $durationInput.val('');
    deleteClicked = true;
    hideDeleteButton();
  });
});
$(document).ready(function () {
  $("#text-generate").on("click", function () {
      if ($("#file_input")[0].files.length > 0) {
          $(".file-drop-area").removeClass("mb-5");
      } else {
          $(".file-drop-area").addClass("mb-5");
      }
  });
  $("#file_input").on("change", function () {
      if ($("#file_input")[0].files.length > 0) {
          $(".file-drop-area").removeClass("mb-5");
      }
  });
});


