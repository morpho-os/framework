<?php
namespace Morpho\Web\View;

use function Morpho\Base\escapeHtml;
use Morpho\Base\NotImplementedException;
use Morpho\Di\IServiceManager;
use Morpho\Di\IServiceManagerAware;
use Morpho\Db\Sql\Db;

class WidgetPlugin implements IServiceManagerAware {
    public function __invoke($name) {
        if ($name !== 'Menu') {
            throw new NotImplementedException();
        }
        $request = $this->serviceManager->get('request');
        return new MenuWidget(
            $this->serviceManager->get('db'),
            $request->getBaseRelUri(),
            $request->getRequestUri()
        );
    }

    public function setServiceManager(IServiceManager $serviceManager) {
        $this->serviceManager = $serviceManager;
    }
}

class MenuWidget {
    public function __construct(Db $db, $baseUri, $requestUri) {
        $this->db = $db;
        $this->baseUri = $baseUri;
        $this->requestUri = trim($requestUri, '/');
    }

    public function renderSystemMenu(array $options = null, array $attributes = null) {
        $options = array_merge(['button' => true], (array)$options);
        $html = '';
        if ($options['button']) {
            $html = '<button class="btn btn-default btn-sm dropdown-toggle navbar-btn" type="button" data-toggle="dropdown" style="margin-left: 1em;"><span class="caret"></span></button>';
        }
        $html .= $this->render(Menu::SYSTEM_NAME, $attributes);
        return $html;
    }

    public function render($name, array $attributes = null) {
        if (null === $attributes) {
            $attributes = ['class' => 'dropdown-menu'];
        }
        $lines = $this->db->selectRows(
            'SELECT r.uri, mi.title
            FROM menu_item mi
            INNER JOIN menu m
                ON mi.menuId = m.id
            INNER JOIN route r
                ON r.module = mi.module AND r.controller = mi.controller AND r.action = mi.action
            WHERE m.name = ?
            ORDER BY mi.weight, mi.title',
            [$name]
        );
        $requestUri = $this->requestUri;
        $baseUri = $this->baseUri;
        $html = '<ul' . TagRenderer::attributes($attributes) . '>';
        foreach ($lines as $line) {
            $html .= '<li><a'
                . ($line['uri'] == $requestUri ? ' class="active"' : '')
                . ' href="' . escapeHtml($baseUri . $line['uri']) . '">' . escapeHtml($line['title']) . '</a></li>';
        }
        $html .= '</ul>';
        return $html;
    }
}
