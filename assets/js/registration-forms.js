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

        navigator.clipboard.writeText(text).then(() => {
            $(button).html("Copied");
            $(button).css("background", "#36c16a");

            setTimeout(() => {
                makeButtonDefault(button);
            }, 2000);
        }, () => {
            $(button).html("Failed!");
            $(button).css("background", "#600dc8");

            setTimeout(() => {
                makeButtonDefault(button);
            }, 2000);
        });
    });
});
