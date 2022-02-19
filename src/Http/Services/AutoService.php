<?php

namespace App\Http\Services;

use ErrorException;
use Exception;
use Symfony\Component\DomCrawler\Crawler;

class AutoService
{
    private string|null $query;
    private string $url = "";
    private string $publicMethod = "search";
    private string $queryParam = "pcode";
    private Crawler $crawler;

    public function __construct()
    {
        $this->crawler = new Crawler(uri: $this->url);
    }

    public function setQuery(string|null $query): void
    {
        $this->query = $query;
    }

    protected function request(): bool|string|array
    {

        $fullUrl = $this->url . '/' . $this->publicMethod . '/?' . $this->queryParam . '=' . $this->query;

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

        return $content;
    }

    public function getItems(): array
    {
        return $this->parse();
    }

    protected function parse()
    {
        $result = $this->request();

        if (is_array($result)) {
            return $result;
        }

        $this->crawler->addHtmlContent($result);

        $catched = $this->crawler->filter('html > body > form#form4mcRecaptcha')->count();
        if ($catched > 0) {
            return ['code' => 502, 'message' => 'Bad Gateway'];
        }


        $tableBody = $this
            ->crawler
            ->filter(
                'html > body.searchPage > 
                        div.siteWrapper > section.siteSection > 
                        div.siteSectionIn > div.baseContent > 
                        noindex > div#searchResultsHtml > 
                        table#searchResultsTable.globalResult > tbody'
            );

        $tr = $tableBody->filter('tr.resultTr2');

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
                "count" => !empty($count) ?  trim($count[0]) : $count,
                "time" => !empty($time) ? trim($time[0]) : $time,
                "img" => !empty($image) ? trim($image[0]) : $image,
                "id" => !empty($id) ? trim($id[0]) : $id,
            ];
        });

//        if (empty($items)){
//            $brandName = $tableBody->filter(
//                'tr.resultTr2 > td.resultBrand.resultInline >
//                        div.favorite-brand > a.open-abcp-modal-info'
//            )->extract(['_text']);
//
//            $brandName = !empty($brandName) ?
//                $brandName :
//                $tableBody->filter(
//                    'tr.resultTr2 > td.resultBrand.resultInline >
//                            div > a.open-abcp-modal-info > '
//                )->extract(['_text']);
//
//            $name = $tr->filter('td.resultDescription ')->extract(['_text']);
//            $price = $tr->filter('td.resultPrice')->extract(['_text']);
//            $article = $tr->filter('td.resultInline.resultPartCode')->extract(['_text']);
//            $count = $tr->filter('td.resultAvailability')->extract(['_text']);
//            $time = $tr->filter('td.resultDeadline')->extract(['_text']);
//            $id = $tr
//                ->filter('td.resultOrder.goodsQuantityBuyWrapper > input.addToBasketLinkFake')
//                ->extract(['searchresultuniqueid']);
//
//            $image = $tr
//                ->filter('td.fr-text-center.resultImage.resultInline > img.searchResultImg.abcp-image-preview')
//                ->extract(['data-image-full']);
//
//            $items[] = [
//                "name" => !empty($name) ? trim($name[0]) : $name,
//                "price" => !empty($price) ? trim($price[0]) : $price,
//                "article" => !empty($article) ? trim($article[0]) : $article,
//                "brand" => !empty($brandName) ? trim($brandName[0]) : $brandName,
//                "count" => !empty($count) ?  trim($count[0]) : $count,
//                "time" => !empty($time) ? trim($time[0]) : $time,
//                "img" => !empty($image) ? trim($image[0]) : $image,
//                "id" => !empty($id) ? trim($id[0]) : $id,
//            ];
//        }

        if (empty($items)){
            return [
                'code' => 404,
                'message' => 'Not Found',
            ];
        }

        return $items;
    }

}
