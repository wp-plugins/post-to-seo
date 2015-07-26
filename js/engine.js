
function PostTOSEOEngine() {

    var serverPath = '/wp-admin/admin-ajax.php';

    this.start = function ()
    {
        getGenerateButton().unbind('click').click(function () {

            AJAXCall({
                url: serverPath,
                type: "POST",
                data: {
                    action: 'post_to_seo_generate',
                    post_title: getPostTitle().val(),
                    post_content: jQuery(tinyMCE.activeEditor.getContent()).text(),
                    lang: getLanguageOptions().val()
                },
                success: function (response) {
                    var data = jQuery.parseJSON(response);
                    var seo = data.seo;
                    
                    getKeywordsArea().html(seo.keywords);
                    getTagsArea().html(seo.tags);
                    getDescriptionArea().html(seo.description);
                }
            });
        });
    };

    var getLanguageOptions = function ()
    {
        return jQuery('#post_to_seo_lang');
    };

    var getGenerateButton = function ()
    {
        return jQuery('.post_to_seo_generate');
    };
    
    var getKeywordsArea = function ()
    {
        return jQuery('#post_to_seo_keywords');
    };
    
    var getTagsArea = function ()
    {
        return jQuery('#post_to_seo_tags');
    };
    
    var getDescriptionArea = function ()
    {
        return jQuery('#post_to_seo_description');
    };

    var getPostTitle = function ()
    {
        return jQuery('#title');
    };

    var AJAXCall = function (dataObject)
    {
        if (dataObject)
        {
            request = jQuery.ajax(dataObject);
        }
        else
        {
            console.log('No data provided for this ajax call!');
        }
    };

};//END OF CLASS