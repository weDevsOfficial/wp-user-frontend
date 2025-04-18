;jQuery(function($) {
    const makeButtonDefault = (button) => {
        $(button).html("Copy");
        $(button).css("background", "#000");
        $(button).css("color", "#fff");
    };

    // select the shortcode with single click
    $(".wpuf-shortcode-area code").on("click", () => {
        let range = document.createRange();
        range.selectNodeContents($(".wpuf-shortcode-area code")[0]);
        let selection = window.getSelection();
        selection.removeAllRanges();
        selection.addRange(range);
    });

    // copy the shortcode once the copy button is clicked
    $(".wpuf-shortcode-area button.button-copy").on("click", () => {
        const text = $(".wpuf-shortcode-area code").text();
        const button = ".wpuf-shortcode-area button.button-copy";

        try {
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text)
                    .then(() => {
                        $(button).html("Copied");
                        $(button).css("background", "#36c16a");
                    })
                    .catch(() => {
                        throw new Error("Clipboard API failed");
                    });
            } else {
                const textarea = document.createElement('textarea');
                textarea.value = text;
                textarea.style.position = 'fixed';
                document.body.appendChild(textarea);
                textarea.select();
                
                const successful = document.execCommand('copy');
                document.body.removeChild(textarea);
                
                if (successful) {
                    $(button).html("Copied");
                    $(button).css("background", "#36c16a");
                } else {
                    throw new Error("execCommand failed");
                }
            }
        } catch (err) {
            $(button).html("Failed!");
            $(button).css("background", "#600dc8");
        } finally {
            setTimeout(() => {
                makeButtonDefault(button);
            }, 2000);
        }
    });
});
