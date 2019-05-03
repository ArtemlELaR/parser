<header>
    <meta charset="utf-8">
    <header>

        <body>

            <?php
            set_time_limit(0);
            require_once 'simplehtmldom/simple_html_dom.php'; // БИБЛИОТЕКА ПАРСИНГА


            /*
            if ((int)(file_get_contents('end.txt'))) {


                $fptime = fopen('list-shop.txt', 'a');   // Метка того что скрипт дошел до своего конца работы. Дальше магазины старше
                fwrite($fptime, "Последний магазин, скрипт больше не работает \r\n");
                fclose($fptime);
                die;
            }

            */

            $fptime = fopen('list-shop.txt', 'a');


            $apikey = ''; // Ключ API

            $shopid = (int)(file_get_contents('counterid.txt')); // Счетчик откуда будет изначальное движение

            $shopnextid = $shopid + 30000000; // Количесвто пройденных магазинов





            while ($shopid < $shopnextid) : // Цикл по айдишникам


                $url = 'https://openapi.etsy.com/v2/shops/' . $shopid . '?api_key=' . $apikey; // Получение данных о магазинах
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $response_body = curl_exec($ch);
                $response = json_decode($response_body);
                $shop = $response->results;

                //echo '<pre>'; 
                //print_r ($shop);

                if (gettype($shop) != 'array') {
                    $shopid += 1;
                    continue;
                } // Пропуск пустого id

                $shopurl = null; // Переменная адреса одного магазина
                $shoptime = null; // метка времении магазина
                $shopdate = null; // метка времени в нормальной форме
                $shopproduct = null; // Количесвтво товаров
                $list = null; // Переменная записи
                $shopname = null; // Название магазина
                $shopsales = null; // метка продаж
                $shopreviews = null; // метка отзывов
                $shoplike = null;  // метка кайков
                $shopadress = null;  // метка адреса



                $shopproduct = $shop[0]->listing_active_count; // Получение количесва продуктов -------------------------------------

                $shoptime =  $shop[0]->creation_tsz; //Получение времени создания магазина 

                if ($shopproduct <= 0) {
                    $shopid += 1;
                    continue;
                }  // Пропускаем магазины у которых нет товаров

                if ($shoptime <= 1514764800) {
                    $shopid += 1;
                    continue;
                }  // Пропускаем магазины раньше 2018
                


                
                $shopurl =  $shop[0]->url; // получение адреса магазина 


                $shopurl = substr($shopurl, 0, strpos($shopurl, '?')); // Чистака адреса от примесей ----------------------------------------------------------------


                $html  = file_get_html($shopurl); // Получение ресурса магазина в DOM; // Получение ресурса магазина в DOM

                foreach ($html->find('a[class=text-gray-lighter]') as $key => $value) :

                    $shopsales = (int)($value->innertext);  //ЗАПИСЬ Количесва продаж ----------------------------------------------------------------

                    
                endforeach;


                if (!$shopsales) {


                    foreach ($html->find('span[class=mr-xs-2 pr-xs-2 br-xs-1]') as $key => $value) :

                        $shopsales = (int)($value->innertext);  //ЗАПИСЬ Количесва продаж ----------------------------------------------------------------
    
                        
                    endforeach;


                }


                if ($shopsales < 5) {
                        $shopid += 1;
                        continue;
                    }

                foreach ($html->find('span[class=total-rating-count text-gray-lighter ml-xs-1]') as $key => $value) :

                    $shopreviews = $value->innertext;  //ЗАПИСЬ отзывов ----------------------------------------------------------------
                    $shopreviews = str_replace(' ','', $shopreviews);
                    $shopreviews = str_replace('(','', $shopreviews);
                    $shopreviews = str_replace(')','', $shopreviews);
                    $shopreviews = (int)$shopreviews;

                endforeach;

                foreach ($html->find('span[class=shop-location mr-xs-2 pr-xs-2 br-xs-1]') as $key => $value) :

                    $shopadress = $value->innertext;  //ЗАПИСЬ адреса ----------------------------------------------------------------

                    

                endforeach;

                $html->clear();
                unset($html);





                $shopname = $shop[0]->shop_name; // Получение имени магазина ----------------------------------------------------------------

                $shoplike = $shop[0]->num_favorers; // Количество лайков магазина ----------------------------------------------------------------

                

                $shopdate = date('d-m-Y', $shoptime); //Получение времени создания магазина ----------------------------------------------------------------

                if(!$shopadress) {$shopadress = 'нет';}
                if(!$shoplike) {$shoplike = 'нет';}
                if(!$shopreviews) {$shopreviews = 'нет';}

                $list = "id: $shopid||Имя: $shopname||Url: $shopurl  ||Продажи: $shopsales||Отзывы: $shopreviews||Лайки: $shoplike||Адрес: $shopadress||Дата: $shopdate||Продуктов: $shopproduct\r\n";

                fwrite($fptime, $list);




                $shopid += 1;
            endwhile;

            fclose($fptime);


            $fptime = fopen('counterid.txt', 'w');   // Метка того что скрипт дошел до своего конца работы. Дальше магазины старше
            fwrite($fptime, $shopnextid);
            fclose($fptime);





            ?>





        </body>
