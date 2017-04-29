<?php
namespace Webiik;

class Admin
{
    private $translation;
    private $render;

    /**
     * Controller constructor.
     */
    public function __construct(WTranslation $translation, WRender $render)
    {
        $this->translation = $translation;
        $this->render = $render;
    }

    public function run()
    {
        // Get merged translations
        // We always get all shared translations and translations only for current page,
        // because Skeleton save resources and adds only these data to Translation class
        $translations = $this->translation->_tAll(false);

        // Parse some values
        $translations['t1'] = $this->translation->_p('t1', ['timeStamp' => time()]);

        // Render page
        echo $this->render->render(['admin', $translations]);
    }
}