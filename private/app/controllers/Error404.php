<?php

namespace Webiik;

class Error404
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
        $this->translation->loadTranslations('404');
        $translations = $this->translation->_tAll(false);

        // Parse some values
        $translations['t1'] = $this->translation->_p('t1', ['timeStamp' => time()]);

        // Render page
        echo $this->render->render(['404', $translations]);
    }
}