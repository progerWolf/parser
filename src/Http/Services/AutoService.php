<?php

namespace App\Http\Services;

use ErrorException;
use Exception;
use Symfony\Component\DomCrawler\Crawler;

class AutoService
{
    /**
     * Искомая строка
     */
    protected string|null $query;

    /**
     * URL сервиса
     */
    protected string $url = "";

    /**
     * Метод покоторому будет делаться запрос
     */
    protected string $publicMethod = "search";

    /**
     * Параметр запроса
     */
    protected string $queryParam = "pcode";

    /**
     * Объект для парсинга данных из HTML/XML
     */
    protected Crawler $crawler;

    /**
     * Сетер
     */
    public function setQuery(string|null $query): void
    {
        $this->query = $query;
    }

    /**
     * Метод для совершение запроса на сервис и получение данных
     *
     * @return bool|string|array
     * @throws ErrorException
     */
    protected function request(string|null $fullUrl = null): bool|string|array
    {
        $this->crawler = new Crawler(uri: $this->url);

        $fullUrl = $fullUrl ?? $this->url . '/' . $this->publicMethod . '/?' . $this->queryParam . '=' . $this->query;

        set_error_handler(
        /**
         * @throws ErrorException
         */ static function ($severity, $message, $file, $line) {
                throw new ErrorException($message, $severity, $severity, $file, $line);
            }
        );

        try {
            $content = file_get_contents($fullUrl);
        }
        catch (Exception $e) {
            return ['code' => 503, 'message' => 'Connection failed'];
        }
        restore_error_handler();

        $this->crawler->addHtmlContent($content);

        $caught = $this->crawler->filter('html > body > form#form4mcRecaptcha')->count();

        if ($caught > 0) {
            return ['code' => 502, 'message' => 'Bad Gateway'];
        }

        return $content;
    }

    /**
     * Метод возврашения спарсенных данных
     */
    public function getItems(): array
    {
        $items = $this->parse();

        if (empty($items)){
            return [
                'code' => 404,
                'message' => 'Not Found',
            ];
        }

        return $items;
    }

    /**
     * Метод реализации парсинга данных из результат сделанного запроса на сервис
     */
    protected function parse(string|null $fullUrl = null): array
    {
        $items = [];
        $result = $this->request($fullUrl);

        if (is_array($result)) {
            return $result;
        }

        $noindex = $this
            ->crawler
            ->filter(
                'html > body.searchPage > 
                        div.siteWrapper > section.siteSection > 
                        div.siteSectionIn > div.baseContent > 
                        noindex'
            );

        $itemTableBody = $noindex->filter('div#searchResultsHtml > 
                                        table#searchResultsTable.globalResult > tbody');

        $tr = $itemTableBody->filter('tr.resultTr2');

        if ($tr->count() !== 0) {

            $items = $tr->each(static function (Crawler $item) {

                $brandName = $item->filter(
                    'td.resultBrand.resultInline >
                        div.favorite-brand > a.open-abcp-modal-info'
                )->extract(['_text']);

                $brandName = !empty($brandName) ?
                    $brandName :
                    $item->filter(
                        'td.resultBrand.resultInline > 
                            div > a.open-abcp-modal-info'
                    )->extract(['_text']);

                $name = $item->filter('td.resultDescription ')->extract(['_text']);
                $price = $item->filter('td.resultPrice')->extract(['_text']);
                $article = $item->filter('td.resultInline.resultPartCode')->extract(['_text']);
                $count = $item->filter('td.resultAvailability')->extract(['_text']);
                $time = $item->filter('td.resultDeadline')->extract(['_text']);
                $id = $item
                    ->filter('td.resultOrder.goodsQuantityBuyWrapper > input.addToBasketLinkFake')
                    ->extract(['searchresultuniqueid']);

                $image = $item
                    ->filter('td.fr-text-center.resultImage.resultInline > img.searchResultImg.abcp-image-preview')
                    ->extract(['data-image-full']);

                return [
                    "name" => !empty($name) ? trim($name[0]) : $name,
                    "price" => !empty($price) ? trim($price[0]) : $price,
                    "article" => !empty($article) ? trim($article[0]) : $article,
                    "brand" => !empty($brandName) ? trim($brandName[0]) : $brandName,
                    "count" => !empty($count) ? trim($count[0]) : $count,
                    "time" => !empty($time) ? trim($time[0]) : $time,
                    "img" => !empty($image) ? trim($image[0]) : $image,
                    "id" => !empty($id) ? trim($id[0]) : $id,
                ];
            });
        }

        if ($tr->count() === 0){
            $tr = $noindex->filter('table.globalCase > tbody > tr.startSearching')->eq(0);

            if ($tr->filter('td')->count() !== 0){
                $link = $tr->filter('td.caseUrl > a.startSearching')->link();
                return $this->parse($link->getUri());
            }
        }

        return $items;
    }

}
