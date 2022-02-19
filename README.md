# parser
PHP 8.1 Docker Nginx

Write a function that takes a text string as input - the one that we print into 
the search string on the site to search for a product.

The result of the function is a multidimensional array (can be represented as a table), 
the rows of which are the goods from the online store http://shop.example

Each product must have the following parameters: 
article, 
brand, 
product name, 
price, 
link to the image (if any), 
availability (quantity), 
delivery time, 
offer code (written in the "searchresultuniqueid" attribute of the hidden field with the "addToBasketLinkFake" class)

Products must match the search results for a given string on the 
site (i.e., you need to make a complete imitation of the user's work on the site).
Write the output array (the result of the function operation) in JSON format to an arbitrary file.

The program should work correctly for any input string.

Example:
Input string: "OC90"
Result (displayed only 2 products for example, of course, you need to add all that are on the page):

[code]
[
    {
        "name": "Oil filter KNECHT/MAHLE OC90",
        "price": 317,
        "article": "OC90",
        "brand": "Mahle/Knecht",
        "count": "200",
        "time": "In stock",
        "img": "http://example.shop/iasdasd.img",
        "id": "ovijaser90wu4jojsfv",
    },
    {
        "name": "Oil filter OPEL AstF/G/VecA/OmA/B NEXIA GANZ GIR01009",
        "price": 185,
        "article": "GIR01009",
        "brand": "GANZ",
        "count": "200",
        "time": "In stock",
        "img": "http://example.shop/",
        "id": "asdfvm30984jgafv",
    },
]
[code]
