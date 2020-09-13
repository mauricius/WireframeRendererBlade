<?php

declare(strict_types=1);

namespace ProcessWire;

use Jenssegers\Blade\Blade;

/**
 * Wireframe Renderer Blade
 *
 * @version 0.1.0
 * @author Maurizio Bonani <maurizio.bonani@gmail.com>
 * @license Mozilla Public License v2.0 https://mozilla.org/MPL/2.0/
 */
class WireframeRendererBlade extends Wire implements Module
{
    /**
     * The Blade instance.
     *
     * @var Blade
     */
    protected $blade;

    /**
     * Default extension.
     *
     * @var string
     */
    protected $ext = 'blade.php';

    /**
     * Init method
     *
     * @param array $settings Additional settings.
     * @return WireframeRendererBlade
     */
    public function ___init(array $settings = []): WireframeRendererBlade
    {
        // autoload Blade classes
        if (!class_exists('\Jenssegers\Blade\Blade')) {
            require_once(__DIR__ . '/vendor/autoload.php' /*NoCompile*/);
        }

        $this->blade = $this->initBlade($settings);

        return $this;
    }

    /**
     * Init Blade
     *
     * @param array $settings Blade settings.
     * @return Blade
     */
    public function ___initBlade(array $settings = []): Blade
    {
        $wireframe = $this->wire('modules')->get('Wireframe');

        $viewPaths = $wireframe->getViewPaths();

        $views = $settings['views'] ?? $viewPaths['view'];
        $cache = $settings['cache'] ?? $this->wire('config')->paths->cache . 'WireframeRendererBlade';

        $blade = new Blade($views, $cache);

        $blade->addNamespace('layout', $viewPaths['layout']);
        $blade->addNamespace('partial', $viewPaths['partial']);
        $blade->addNamespace('component', $viewPaths['component']);

        return $blade;
    }

    /**
     * Render method
     *
     * @param string $type Type of file to render (view, layout, partial, or component).
     * @param string $view Name of the view file to render.
     * @param array $context Variables used for rendering.
     * @return string Rendered markup.
     * @throws WireException if param $type has an unexpected value.
     */
    public function render(string $type, string $view, array $context = []): string
    {
        if (! in_array($type, array_keys($this->wire('modules')->get('Wireframe')->getViewPaths()))) {
            throw new WireException(sprintf('Unexpected type (%s).', $type));
        }

        if ($type !== 'view') {
            $view = $this->namespaceView($type, $view);
        }

        $view = $this->adaptView($view);

        return $this->blade->make($view, $context)->render();
    }

    /**
     * Namespace the view.
     *
     * @param  string $type
     * @param  string $view
     * @return string
     */
    protected function namespaceView(string $type, string $view)
    {
        return $type . '::' . $view;
    }

    /**
     * Adapt view path to Blade notation.
     *
     * @param  string $view
     * @return string
     */
    protected function adaptView($view)
    {
        return preg_replace('/.'. $this->ext . '$/', '', str_replace('/', '.', $view));
    }

    /**
     * @return Blade
     */
    public function getBladeInstance(): Blade
    {
        return $this->blade;
    }

    /**
     * @return string
     */
    public function getExt(): string
    {
        return $this->ext;
    }
}
