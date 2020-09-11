<?php
$page_404=0;
$r=1;
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

session_start();
$usid = 0;
if (isset($_SESSION['usid'])) {
    $usid=intval($_SESSION['usid']);
}
 
include "conf.php";

if ($page_404 == 0) {
    header('Content-type: text/html; charset=UTF-8');
} else {
    header("HTTP/1.0 404 Not Found");
}


// Назначаем модуль и действие по умолчанию.
$module = 'index';
$action = 'index';
// Массив параметров из URI запроса.
$params = array();

// Если запрошен любой URI, отличный от корня сайта.
if ($_SERVER['REQUEST_URI'] != '/') {
    try {
        // Для того, что бы через виртуальные адреса можно было также передавать параметры
        // через QUERY_STRING (т.е. через "знак вопроса" - ?param=value),
        // необходимо получить компонент пути - path без QUERY_STRING.
        // Данные, переданные через QUERY_STRING, также как и раньше будут содержаться в 
        // суперглобальных массивах $_GET и $_REQUEST.
        $url_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Разбиваем виртуальный URL по символу "/"
        $uri_parts = explode('/', trim($url_path, ' /'));
        
        if (count($uri_parts) > 0) {
            
            $authorized_url = array(
                "index.php",
                "reg_form",
                "uscreate",
				"enter",
                "favorites",
                "search",
				"payprocessing"
            ); //Страницы которые всегда есть на сайте
            if (in_array($uri_parts[0], $authorized_url)) {
                
                if ($uri_parts[0] == 'index.php') {
                    header("HTTP/1.1 301 Moved Permanently");
                    header("Location: /");
                }
                
                if ($uri_parts[0] == 'exit') {                    
                    $usid = 0;
                    $_session['usid']=0;
					header("HTTP/1.1 301 Moved Permanently");
                    header("Location: /");
                }

                if ($uri_parts[0] == 'search') { //Страница результатов поиска
                    $mod_search = 1;
                }
                
                if ($uri_parts[0] == 'uscreate') { //Страница регистрации пользователя
                    
					$str_name_cr=''; if (isset($_POST['name_cr'])) {$str_name_cr=$_POST['name_cr']; }
					$str_phone_cr=''; if (isset($_POST['phone_cr'])) {$str_phone_cr=$_POST['phone_cr']; }
					$str_sekret_cr=''; if (isset($_POST['sekret_cr'])) {$str_sekret_cr=$_POST['sekret_cr']; }
					$str_lat_cr=''; if (isset($_POST['lat_cr'])) {$str_lat_cr=$_POST['lat_cr']; }
					$str_lng_cr=''; if (isset($_POST['lng_cr'])) {$str_lng_cr=$_POST['lng_cr']; }

					if (strlen($str_phone_cr)>5) {
					  $us1 = select("SELECT count(*) as cnt from usr WHERE sekret ='".mysql_real_escape_string($str_sekret_cr)."' and phone='".mysql_real_escape_string($str_phone_cr)."'");
                                    if ($us1[0]['cnt'] == 0) {
                                        mysql_query_v("insert into usr (phone, sekret, name) values ('".mysql_real_escape_string($str_phone_cr)."','".mysql_real_escape_string($str_sekret_cr)."','".mysql_real_escape_string($str_name_cr)."')");
  
					  $us1 = select("SELECT id from usr WHERE sekret ='".mysql_real_escape_string($str_sekret_cr)."' and phone='".mysql_real_escape_string($str_phone_cr)."'");
                                    if ($us1[0]['id'] > 0) {
                                          $usid = $us1[0]['id'];
                                          $_session['usid']=$us1[0]['id'];
                                          }                                 
									}

									
					
					}
					//INSERT INTO `usr`(`id`, `phone`, `sekret`, `name`, `lat`, `lng`) VALUES ([value-1],[value-2],[value-3],[value-4],[value-5],[value-6])
                }
                
                if ($uri_parts[0] == 'favorites') { //Избранное
                    $mod_user_favorites = 1;
                }
                
                
               
                if ($uri_parts[0] == 'reg_form') {
                    include "regform.php";
                } //
                //Если имя совпало - ничегоне делаем
                //echo "Совпало";
            } else {
                //Если не совпало - ищем в базе
                //Проверка на категорию             
             
            }
        }
        
        $module = array_shift($uri_parts); // Получили имя модуля
        $action = array_shift($uri_parts); // Получили имя действия
        
        // Получили в $params параметры запроса
        for ($i = 0; $i < count($uri_parts); $i++) {
            $params[$uri_parts[$i]] = $uri_parts[++$i];
        }
    }
    catch (Exception $e) {
        $module = '404';
        $action = 'main';
    }
}



//Авторизация
if (isset($_POST['phone_log']))
{
  if (isset($_POST['sekret_log']))
    {
  $us2 = select("SELECT id from usr WHERE sekret ='".mysql_real_escape_string($_POST['sekret_log'])."' and phone='".mysql_real_escape_string($_POST['phone_log'])."' limit 1");
                if (isset($us2[0])) {
									if ($us2[0]['id'] > 0) {
                                          $usid = $us2[0]['id'];
                                          $_session['usid']=$us2[0]['id'];
                                          }                                 
				}
	}
}

//echo "Сайт временно не работает. Ведутся работы";
//exit;

    $title       = 'Выбираем работу и сотрудников';
    $description = 'Здесь вы сможете подобрать работу в Минске и регионах';

    $tov_to_page = 30; 
	$page        = "1"; 
	$search='';

    



    $info_block='<div class="price-info text-center">
						<p>Стоимость заказа без учета доставки: <span class="BYN">117,99 руб.</span> <span class="BYR">1 179 900 руб.</span></p>
					</div>';





//==Блок навигации==
if (isset($_GET['page'])) {
    $page           = intval($_GET['page']);
    $meta_title_add = " cтраница " . $page;
} else {
    $page = "1";
}
if ($page == "0" or $page == "") {
    $page = "1";
}

//Поиск по сайту
if (isset($_GET)) {
  if (isset($_GET['q'])) {  //q
   $search = $_GET['q'];    //q
   $search = addslashes($search);
   $search = htmlspecialchars($search);
   $search = stripslashes($search);
  }
}

$arrReplace = array('q'=>'й', 'w'=>'ц', 'e'=>'у', 'r'=>'к', 't'=>'е', 'y'=>'н', 'u'=>'г', 'i'=>'ш', 'o'=>'щ', 'p'=>'з', '['=>'х', ']'=>'ъ', 'a'=>'ф', 's'=>'ы', 'd'=>'в', 'f'=>'а', 'g'=>'п', 'h'=>'р', 'j'=>'о', 'k'=>'л', 'l'=>'д', ';'=>'ж', "'"=>'э', 'z'=>'я', 'x'=>'ч', 'c'=>'с', 'v'=>'м', 'b'=>'и', 'n'=>'т', 'm'=>'ь', ','=>'б', '.'=>'ю', '/'=>'.', '`'=>'ё', 'Q'=>'Й', 'W'=>'Ц', 'E'=>'У', 'R'=>'К', 'T'=>'Е', 'Y'=>'Н', 'U'=>'Г', 'I'=>'Ш', 'O'=>'Щ', 'P'=>'З', '{'=>'Х', '}'=>'Ъ', 'A'=>'Ф', 'S'=>'Ы', 'D'=>'В', 'F'=>'А', 'G'=>'П', 'H'=>'Р', 'J'=>'О', 'K'=>'Л', 'L'=>'Д', ':'=>'Ж', '"'=>'Э', '|'=>'/', 'Z'=>'Я', 'X'=>'Ч', 'C'=>'С', 'V'=>'М', 'B'=>'И', 'N'=>'Т', 'M'=>'Ь', '<'=>'Б', '>'=>'Ю', '?'=>',', '~'=>'Ё', '@'=>'"', '#'=>'№', '$'=>';', '^'=>':', '&'=>'?');

$arrReplace2 = array('й'=>'q', 'ц'=>'w', 'у'=>'e', 'к'=>'r', 'е'=>'t', 'н'=>'y', 'г'=>'u', 'ш'=>'i', 'щ'=>'o', 'з'=>'p', 'х'=>'[', 'ъ'=>']', 'ф'=>'a', 'ы'=>'s', 'в'=>'d', 'а'=>'f', 'п'=>'g', 'р'=>'h', 'о'=>'j', 'л'=>'k', 'д'=>'l', 'ж'=>';', 'э'=>"'", 'я'=>'z', 'ч'=>'x', 'с'=>'c', 'м'=>'v', 'и'=>'b', 'т'=>'n', 'ь'=>'m', 'б'=>',', 'ю'=>'.', '.'=>'/', 'ё'=>'`', 'Й'=>'Q', 'Ц'=>'W', 'У'=>'E', 'К'=>'R', 'Е'=>'T', 'Н'=>'Y', 'Г'=>'U', 'Ш'=>'I', 'Щ'=>'O', 'З'=>'P', 'Х'=>'{', 'Ъ'=>'}', 'Ф'=>'A', 'Ы'=>'S', 'В'=>'D', 'А'=>'F', 'П'=>'G', 'Р'=>'H', 'О'=>'J', 'Л'=>'K', 'Д'=>'L', 'Ж'=>':', 'Э'=>'"', '/'=>'|', 'Я'=>'Z', 'Ч'=>'X', 'С'=>'C', 'М'=>'V', 'И'=>'B', 'Т'=>'N', 'Ь'=>'M', 'Б'=>'<', 'Ю'=>'', ','=>'?', 'Ё'=>'~', '"'=>'@', '№'=>'#', ';'=>'$', ':'=>'^', '?'=>'&');


$search =str_replace('-', '', trim($search));
$search =str_replace(',', ' ', $search);
$search =str_replace(')', ' ', $search);
$search =str_replace('(', ' ', $search);
$search =str_replace('[', ' ', $search);
$search =str_replace(']', ' ', $search);
$search =str_replace('  ', ' ', $search);
$search =str_replace('.', '', $search);
$search =str_replace(',', '', $search);
$search =str_replace('_', ' ', $search);
$search =str_replace("'", '', $search);


$search2 = strtr($search ,$arrReplace);
$search3 = strtr($search ,$arrReplace2);

//echo $search;
$exp_r = explode(" ", $search);
$cnt_e = count($exp_r); 
$wh='';
$one_fr='';
for ($i = 1; $i < $cnt_e+1; $i++) {
	if ($wh=='') {
		 if (trim($exp_r[$i-1])<>'') {
        $one_fr=$exp_r[$i-1];
		$wh="(name like '%".mysql_real_escape_string($exp_r[$i-1])."%' or name like '%".mysql_real_escape_string(strtr($exp_r[$i-1] ,$arrReplace))."%' or name like '%".mysql_real_escape_string(strtr($exp_r[$i-1] ,$arrReplace2))."%' or city like '%".mysql_real_escape_string($exp_r[$i-1])."%' or city like '%".mysql_real_escape_string(strtr($exp_r[$i-1] ,$arrReplace))."%' or city like '%".mysql_real_escape_string(strtr($exp_r[$i-1] ,$arrReplace2))."%' or responsibility like '%".mysql_real_escape_string($exp_r[$i-1])."%' or responsibility like '%".mysql_real_escape_string(strtr($exp_r[$i-1] ,$arrReplace))."%' or responsibility like '%".mysql_real_escape_string(strtr($exp_r[$i-1] ,$arrReplace2))."%')";   
		 }
		} else {
			if (trim($exp_r[$i-1])<>'') {
        $wh=$wh." and (name like '%".mysql_real_escape_string($exp_r[$i-1])."%' or name like '%".mysql_real_escape_string(strtr($exp_r[$i-1] ,$arrReplace))."%' or name like '%".mysql_real_escape_string(strtr($exp_r[$i-1] ,$arrReplace2))."%' or city like '%".mysql_real_escape_string($exp_r[$i-1])."%' or city like '%".mysql_real_escape_string(strtr($exp_r[$i-1] ,$arrReplace))."%' or city like '%".mysql_real_escape_string(strtr($exp_r[$i-1] ,$arrReplace2))."%' or responsibility like '%".mysql_real_escape_string($exp_r[$i-1])."%' or responsibility like '%".mysql_real_escape_string(strtr($exp_r[$i-1] ,$arrReplace))."%' or responsibility like '%".mysql_real_escape_string(strtr($exp_r[$i-1] ,$arrReplace2))."%')"; 
			}
		}
}
if ($wh=="") { $wh="1=1";}

function mysql_query_v($str)
{
   // echo $str." *************  ";
   // $begin_time = time() - 1272000000 + floatval(microtime());
   // $b=mysql_query($str);
   // $end_time = time() - 1272000000 + floatval(microtime()) - $begin_time;
    //echo "<b>".$end_time."</b>"."|".$str;
    return mysql_query($str);
}

function proce_vis($pr_from, $pr_to, $val)
	{
	  $v=' б.р.';
	  if ($val=='USD') {$v=' $';}

	  $result='';
      if (($pr_from<>'') and ($pr_to<>'') and ($pr_from<>'0') and ($pr_to<>'0')) {
	    $result=$pr_from.' - '.$pr_to.''.$v;
	  }
      if (($pr_from=='') or ($pr_to=='') or ($pr_from=='0') or ($pr_to=='0')) {
	    if (($pr_from<>'0') and ($pr_from<>'')) {
		$result=$pr_from.''.$v; }

		if (($pr_to<>'0') and ($pr_to<>'')) {
		$result=$pr_to.''.$v; }
	  }
	  if (trim($result)=='') {
	    $result='не указана';
	  }

	  return $result;
	}


function select($z) //Из запроса в массив
{  $m_r = array();

  // echo $z;

   $z_s = mysql_query_v($z);
   while ($z_s2 = mysql_fetch_assoc($z_s)) 
   { $m_r[] = $z_s2; } 
	 
  return $m_r;
}

function page_number($tov_to_page,$page,$zapros, $param_name,$param_value)  //Навишгация по страницам
{
 global $list_nav, $lims, $limf, $maxi;

 //print_r($param_name);
 //print_r($param_value);

 $strdoppar="";
 $count = count($param_name);
 for ($i = 0; $i < $count; $i++) {
  
  if ($strdoppar=="")
	{
     $strdoppar=$strdoppar."".$param_name[$i]."".$param_value[$i]."&"; //1й параметр всегда URL
    } else 
	 {
	 $strdoppar=$strdoppar."".$param_name[$i]."".$param_value[$i]."&";    
	}

 }
 if ($strdoppar=="")
 {
 $strdoppar="?";
 }

 $strdoppar=str_replace (' ','%20',$strdoppar);

 $list_nav='<div class="text-center"><nav aria-label="Page navigation"><ul class="pagination">';  //col-md-12 
 $zkol = $zapros;
// echo $zkol;
 $itog = mysql_query_v($zkol);
    $pos  = mysql_fetch_row($itog);
    $maxi = intval($pos[0]);
  $maxpage = round(($maxi) / $tov_to_page);
    $maxit   = ($maxi) / $tov_to_page;
    if ($maxit > $maxpage) {
        $maxpage = $maxpage + 1;
    }
    if ($page > $maxpage) {
        $page = $maxpage;
    }
  if ($maxi > 0) {
        //$list_nav = $list_nav . "<span></span>"; 
        if ($page >= 4 and $maxpage > 5)
            $list_nav = $list_nav . '<li style="margin-right: 0px;"><a href="/'.$strdoppar.'page=1" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a></li>';//<a href=/".$strdoppar."page=1>1</a> ... ";
        
        $f1 = $page + 2;
        $f2 = $page - 2;
        if ($page == 1) {
            $f1 = $page + 4;
            $f2 = $page;
        }
        if ($page == 2) {
            $f1 = $page + 3;
            $f2 = $page - 1;
        }
        if ($page == $maxpage) {
            $f1 = $page;
            $f2 = $page - 4;
        }
        if ($page == $maxpage - 1) {
            $f1 = $page + 1;
            $f2 = $page - 3;
        }
        if ($maxpage <= 4) {
            $f1 = $maxpage;
            $f2 = 1;
        }
        
        
		for ($i = $f2; $i <= $f1; $i++) {
            
			if ($page == $i) {
				if ($maxpage>1) {
                $list_nav = $list_nav . '<li class="active" style="margin-right: 0px;"><a href="#">'.$i.' <span class="sr-only">(current)</span></a></li>'; }
            } else {
             if ($i>0) {
				$list_nav = $list_nav . '<li style="margin-right: 0px;"><a href=/'.$strdoppar.'page='.$i.'>'.$i.'</a></li>';
			 }
            }
        }
        
        if ($page <= $maxpage - 3 and $maxpage > 5) {
            $list_nav = $list_nav . '<li style="margin-right: 0px;"><a href="/'.$strdoppar.'page='.$maxpage.'" aria-label="Next"><span aria-hidden="true">&raquo;</span></a></li>';//"... <a href=/".$strdoppar."page=$maxpage>$maxpage</a>";
        }
        $list_nav = $list_nav . " ";
    }
    
    $itogopage = $maxi;
    $lims      = ($page * $tov_to_page) - $tov_to_page;
    if ($lims < 0) {
        $lims = 0;
    }
    $limf = $tov_to_page;

$list_nav=$list_nav.'</ul></nav></div>';

return $list_nav;
}


function jobpos($rcat)
{
	
   $line_j='<div class="panel-body">
								<div class="vcenter col-md-3">
									'.$rcat['created_at'].'<hr>
									<b>'.$rcat['employer_name'].'</b><hr>
                                    '.$rcat['department'].'<hr>
                                    '.$rcat['raw'].'<br>
									'.$rcat['metrostations'].'
								</div>
								<div class="vcenter col-md-4">
									<div class="description">
										<p class="name"><a href=""><b>'.$rcat['name'].'</b></a></p>
										<div class="availability">
											<span class="in-stock">'.$rcat['responsibility'].'</span>
										</div>
										<div class="payment-methods">									
											<ul>
												<li>'.$rcat['schedule'].'</li>
											</ul>
										</div>
                                        '.$rcat['requirement'].'
									</div>
								</div>
								<div class="vcenter col-md-2">
									<p class="price"><span>Заработная плата</span>'.proce_vis($rcat['salary_from'], $rcat['salary_to'], $rcat['currency']).'</p>
								</div>
								<div class="vcenter col-md-2">
									<div class="order-quantity">
										<div class="input-group">
											'.str_replace('|', '<br>', $rcat['phone_numb']).'<hr>
											'.str_replace('|', '<br>', $rcat['phone_comment']).'
										</div>
									</div>
								</div>
								
								
							</div>
							<hr><p></p><hr>';
	   return $line_j;						
}


?>

<!DOCTYPE html>
<html class="no-js" lang="ru">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php echo $title; ?></title>
    <meta name="description" content="<?php echo $description; ?>"/>
    <meta name="keywords" content="Работа, Минске, Минск, поиск, трудоустройство, подбор, персоонала"/>
    <meta name="author" content="hr.by">
    <meta name="apple-mobile-web-app-capable" content="yes"/>
<?php
	if ($page_404==1) { echo '    
	<meta name="robots" content="none"/>
	'; }
?>

    <link rel="apple-touch-icon" href="apple-touch-icon.png">
    <link rel="stylesheet" href="css/main.min.css">
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700&amp;subset=cyrillic-ext" rel="stylesheet">
    <meta http-equiv="x-dns-prefetch-control" content="on">
    <link href="//fonts.googleapis.com" rel="dns-prefetch" />
    <link href="//mc.yandex.ru" rel="dns-prefetch" />
    <link href="//oss.maxcdn.com" rel="dns-prefetch" />
    <link href="//www.google-analytics.com" rel="dns-prefetch">
    <link href="//ajax.googleapis.com" rel="dns-prefetch">
    <link rel="shortcut icon" type="image/x-icon" href="/favicon.ico" />
    <!--[if lt IE 9]>
    <p class="browserupgrade">Вы используете устаревший браузер. Обновите браузер.</p>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

		<script src="/js/modernizr-2.8.3-respond-1.4.2.min.js"></script>
	<script type="text/javascript">
      window.dataLayer = window.dataLayer || [];
    </script>

</head>

<body>



    <div class="header">
        <div class="container">
            <div class="row">
                <div role="banner" class="col-lg-2 col-md-6 col-sm-6">
                    <a class="logo" href="/">
                        <img src="img/logo.png" alt="" title="" class="img-responsive">
                    </a>
                </div>
                <div class="search-block col-lg-6 col-md-6 col-sm-6">
                   <form class="search" role="search" action="/search" method="get">
						<div class="form-group">
							<div class="input-group">
								<input class="search form-control" id="search" type="text" name="q" value="<?php echo $search;?>" placeholder="Поиск в каталоге" autocomplete="off">
								
								<span class="input-group-addon">
									<button type="submit" class="btn btn-default search"><span class="sprite sprite-icon-search" aria-hidden="true"></span></button>
								</span>									
							</div>

							 <div id="resSearch">
							 </div>


						</div>
					</form>
     
                </div>
                
            </div>
        </div>
    </div>
    <div class="header-nav">
        <div class="container">
            <div class="row">
                <nav class="navbar">
                    <ul>
                        <div class="cd-dropdown-wrapper">
                            <a class="cd-dropdown-trigger" href="#0">Меню</a>
                            <div class="cd-dropdown">
                                <h2>Меню</h2>
                                <a href="#0" class="cd-close">Закрыть</a>
                                <ul class="cd-dropdown-content" style="background-color: #888fff;">


                                            <li><a class='cd-dropdown-content' href='/'>Поиск работы</a></li>
                                            <li><a class='cd-dropdown-content' href='/search_us'>Поиск сотрудника</a></li>
                                            <li><a class='cd-dropdown-content' href='/new_r'>Новое резюме</a></li>
                                            <li><a class='cd-dropdown-content' href='/new_v'>Добавить вакансию</a></li>

                                </ul>
                            </div>
                        </div>
                    </ul>
                    <ul class="navbar-right">
                        <li><div class="results_fav"> <a href="wishlist"><span class="sprite sprite-icon-wishlist" aria-hidden="true"></span>В избранном: 0</a></div></li>
                        
						<li><div class="results_fav"> <a href="login">Личный кабинет</a></div>
						</li>
						
						<li><div class="results_fav"> <?php
						  if ($usid>0) {echo '<a href="exit">Выход</a>';} else { echo '<a href="enter">Авторизация</a>';}
						?></div>
						</li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
   
<?php

					   
?>


	<div class="container cart">
		<div class="row">
			<div class="col-md-12">
				<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
					<div class="panel panel-default">
						

                       <?php echo $module;
					   if (($module=='index')||($module=='search'))
					   {
						   include "search.php";
					   }
					   
					   if ($module=='login')
					   {
                           include "login.php";
					   }

					   if ($module=='enter')
					   {
                           include "enter.php";
					   }
					   
					   if ($module=='uscreate')
					   {
						 if ($usid>0) {
					     echo header_htm('Регистрация успешно завершена'); } else {
						   echo header_htm('Не удалось создать учетную запись');
						 }
						 include "search.php";
					   }
					   ?>
					

						
					</div>
					
    <footer>
        <div role="contentinfo" class="container text-center copyrights">
            <div class="row">
                <div class="col-md-12">
                    <p>Сайт бесплатных объявлений. Для обратной свзи пишите на info@site.by</p>
                </div>
            </div>
        </div>
    </footer>
    <script src="js/main.min.js"></script>

    
	

	<script src="/js/jquery-2.2.4.min.js"></script>
	<script src="/js/bootstrap.min.js"></script>
	<script src="/js/main.js?asdwr"></script>
	<script src="/js/SmoothScroll.js"></script>
	<script src="/js/owl.carousel.min.js"></script>
	<script src="/js/jquery.maskedinput.min.js"></script>
	<script src='/js/ntsaveforms.js'></script>
	<script src="/js/notie.js"></script>
	<script src="/js/jquery.magnific-popup.min.js"></script>
	<script src="/js/bootstrap-rating-input.min.js"></script>	
	<script src='https://www.google.com/recaptcha/api.js'></script>


<script language="javascript" type="text/javascript">

function showhide(id){
  var e = document.getElementById(id);
  if( e ) e.style.display = e.style.display ? "" : "none";
  }


function debounce(fn, duration) {
  var timer;
  return function(){
    clearTimeout(timer);
    timer = setTimeout(fn, duration);
  }
}


$(function(){

 
 $(document).on('click', function(e) {
    if (!$(e.targer).closest("#resSearch").length) {
		$('.dropdown').hide();
    }
    if (!$(e.targer).closest("#ResPreSearch").length) {
		$('.dropdown').hide();
    }
	e.stopPropagation();
  });



  $('#search').on('keyup', debounce(function(){
  
     var search = $("#search").val();
     if (search.length>2)
     {
     $.ajax({
       type: "POST",
       url: "/search.php",
       data: {"search": search},
       cache: false,                                 
      success: function(response){
          $("#resSearch").html(response);
       }
     });
     } else {
	  $("#resSearch").html("");
	 }
	 
	 return false;
    
  }, 1000));



});

</script>

</body>
</html>