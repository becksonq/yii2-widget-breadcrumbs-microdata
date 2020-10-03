<?php

namespace becksonq\breadcrumbs;

use Yii;
use yii\base\InvalidConfigException;
use yii\bootstrap4\Breadcrumbs as BaseBreadcrumbs;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * Class Breadcrumbs
 * @package common\widgets\breadcrumbs_microdata
 *
 * Breadcrumbs with Microdata markup from Schema.org
 */
class Breadcrumbs extends BaseBreadcrumbs
{
    /**
     * @var string the name of the breadcrumb container tag.
     */
    public $tag = 'ol itemscope itemtype="https://schema.org/BreadcrumbList"';
    /**
     * @var string the template used to render each inactive item in the breadcrumbs. The token `{link}`
     * will be replaced with the actual HTML link for each inactive item.
     */
    public $itemTemplate = "<li class=\"breadcrumb-item\" itemprop=\"itemListElement\" itemscope itemtype=\"https://schema.org/ListItem\">{link}</li>\n";
    /**
     * @var string the template used to render each active item in the breadcrumbs. The token `{link}`
     * will be replaced with the actual HTML link for each active item.
     */
    public $activeItemTemplate = "<li class=\"breadcrumb-item active\" aria-current=\"page\" itemprop=\"itemListElement\" itemscope itemtype=\"https://schema.org/ListItem\">{link}</li>\n";

    /**
     * @return string
     * @throws InvalidConfigException
     */
    public function run()
    {
        $this->registerPlugin('breadcrumb');

        if (empty($this->links)) {
            return '';
        }
        $links = [];
        if ($this->homeLink === null) {
            $links[] = $this->renderItem([
                'label' => Yii::t('yii', 'Home'),
                'url'   => Yii::$app->homeUrl,
            ], $this->itemTemplate, 0);
        } elseif ($this->homeLink !== false) {
            $links[] = $this->renderItem($this->homeLink, $this->itemTemplate, 0);
        }
        foreach ($this->links as $key => $link) {
            if (!is_array($link)) {
                $link = ['label' => $link];
            }
            $links[] = $this->renderItem($link, isset($link['url']) ? $this->itemTemplate : $this->activeItemTemplate,
                ++$key);
        }
        return Html::tag('nav', Html::tag($this->tag, implode('', $links), $this->options),
            $this->navOptions);
    }

    /**
     * @param array $link
     * @param string $template
     * @param null $key
     * @return string
     * @throws InvalidConfigException
     */
    protected function renderItem($link, $template, $key = null)
    {
        $encodeLabel = ArrayHelper::remove($link, 'encode', $this->encodeLabels);
        if (array_key_exists('label', $link)) {
            $label = $encodeLabel ? Html::encode($link['label']) : $link['label'];
        } else {
            throw new InvalidConfigException('The "label" element is required for each link.');
        }
        if (isset($link['template'])) {
            $template = $link['template'];
        }
        $label = '<span itemprop="name">' . $label . '</span><meta itemprop="position" content="' . $key . '">';
        if (isset($link['url'])) {
            $options = $link;
            unset($options['template'], $options['label'], $options['url']);
            $link = Html::a($label, $link['url'], array_merge($options, ["itemprop" => "item"]));
        } else {
            $link = $label;
        }

        return strtr($template, ['{link}' => $link]);
    }
}
