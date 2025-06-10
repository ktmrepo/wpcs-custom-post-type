jQuery(document).ready(function ($) {
  // Copy shortcode functionality
  $(document).on(
    "click",
    ".ccb-copy-shortcode, .ccb-copy-shortcode-link",
    function (e) {
      e.preventDefault();

      var shortcode = $(this).data("shortcode");
      var button = $(this);

      // Try to copy to clipboard
      if (copyToClipboard(shortcode)) {
        showCopySuccess(button);
      } else {
        showCopyFallback(shortcode, button);
      }
    }
  );

  // Copy to clipboard function
  function copyToClipboard(text) {
    // Modern clipboard API
    if (navigator.clipboard && window.isSecureContext) {
      navigator.clipboard
        .writeText(text)
        .then(function () {
          return true;
        })
        .catch(function () {
          return fallbackCopyToClipboard(text);
        });
      return true;
    } else {
      return fallbackCopyToClipboard(text);
    }
  }

  // Fallback copy method for older browsers
  function fallbackCopyToClipboard(text) {
    var textArea = document.createElement("textarea");
    textArea.value = text;
    textArea.style.position = "fixed";
    textArea.style.left = "-999999px";
    textArea.style.top = "-999999px";
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();

    try {
      var successful = document.execCommand("copy");
      document.body.removeChild(textArea);
      return successful;
    } catch (err) {
      document.body.removeChild(textArea);
      return false;
    }
  }

  // Show success message
  function showCopySuccess(button) {
    var originalText = button.text();
    button.text(ccb_ajax.messages.copied);
    button.addClass("copied");

    setTimeout(function () {
      button.text(originalText);
      button.removeClass("copied");
    }, 2000);
  }

  // Show fallback dialog
  function showCopyFallback(shortcode, button) {
    var modal = $(
      '<div class="ccb-copy-modal">' +
        '<div class="ccb-copy-modal-content">' +
        "<h3>Copy Shortcode</h3>" +
        "<p>Please copy the shortcode below:</p>" +
        '<input type="text" value="' +
        shortcode +
        '" readonly onclick="this.select();" />' +
        '<div class="ccb-copy-modal-buttons">' +
        '<button class="button button-primary ccb-modal-close">Close</button>' +
        "</div>" +
        "</div>" +
        "</div>"
    );

    $("body").append(modal);
    modal.find("input").focus().select();

    // Close modal
    modal.on("click", ".ccb-modal-close, .ccb-copy-modal", function (e) {
      if (e.target === this) {
        modal.remove();
      }
    });

    // Prevent closing when clicking inside modal content
    modal.on("click", ".ccb-copy-modal-content", function (e) {
      e.stopPropagation();
    });
  }

  // Add some inline styles for the modal
  $("<style>")
    .prop("type", "text/css")
    .html(
      `
            .ccb-copy-modal {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.5);
                z-index: 9999;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .ccb-copy-modal-content {
                background: white;
                padding: 20px;
                border-radius: 5px;
                max-width: 400px;
                width: 90%;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            }
            .ccb-copy-modal h3 {
                margin-top: 0;
                color: #333;
            }
            .ccb-copy-modal input {
                width: 100%;
                padding: 8px;
                margin: 10px 0;
                border: 1px solid #ddd;
                border-radius: 3px;
                font-family: monospace;
                font-size: 12px;
                background: #f9f9f9;
            }
            .ccb-copy-modal-buttons {
                text-align: right;
                margin-top: 15px;
            }
            .ccb-copy-shortcode.copied {
                background-color: #46b450 !important;
                color: white !important;
            }
        `
    )
    .appendTo("head");
});

// Auto-select shortcode inputs when clicked
$(document).on("click", ".ccb-shortcode-meta input[readonly]", function () {
  $(this).select();
});

// Add tooltips to shortcode inputs
$(".ccb-shortcode-meta input[readonly]").attr("title", "Click to select all");

// Enhanced admin list table functionality
if ($(".wp-list-table").length) {
  // Add copy functionality to admin list
  $(document).on("mouseover", ".ccb-copy-shortcode-link", function () {
    $(this).attr("title", "Click to copy shortcode");
  });
}

// Keyboard shortcut for copying (Ctrl+C when input is focused)
$(document).on("keydown", ".ccb-shortcode-meta input[readonly]", function (e) {
  if (e.ctrlKey && e.keyCode === 67) {
    // Ctrl+C
    var shortcode = $(this).val();
    if (copyToClipboard(shortcode)) {
      showCopySuccess($(this).siblings(".ccb-copy-shortcode").first());
    }
  }
});

// Add visual feedback for the admin columns
$(document).on("click", ".column-shortcode code", function () {
  $(this).addClass("selected");
  setTimeout(function () {
    $(".column-shortcode code").removeClass("selected");
  }, 1000);
});

// Add selected style
$("<style>")
  .prop("type", "text/css")
  .html(
    `
            .column-shortcode code.selected {
                background-color: #0073aa !important;
                color: white !important;
            }
        `
  )
  .appendTo("head");
