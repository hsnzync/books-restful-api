<?php
require_once 'config.php';

$BASE_URI = "/API/";
$url = 'https://stud.hosted.hr.nl/0892980/API/books/';
$method = $_SERVER["REQUEST_METHOD"];
$accept = $_SERVER["HTTP_ACCEPT"];

if(isset($_GET['id'])){
    $id = $_GET['id'];
}

$limit;
$start = 1;
$pagenr = 1;

switch ($method) {

    case "GET":
        $detailId = "";
        $total = 0;
        $items = array();
        $links = array();

        if(isset($_GET['id'])){
            $detailId = $_GET['id'];

            $queryId = "SELECT * FROM Boekencollectie WHERE id = '$detailId'";
            $result = mysqli_query($db, $queryId) or die(mysqli_error($db));

            if(mysqli_num_rows($result) > 0) {
                while($item = mysqli_fetch_assoc($result)) {
                    $id = $item['id'];
                    $links = array(
                        'links' => array(
                            array('rel' => 'self',
                                  'href' => $url.$id),
                            array('rel' => 'collection',
                                  'href' => $url)
                        )
                    );

                    $items = $item+$links;
                }
            }
            else
            {
                http_response_code(404);
            }
        }
        else
        {
            $query = "SELECT * FROM Boekencollectie";
            $result = mysqli_query($db, $query) or die(mysqli_error($db));

            $totalItems = mysqli_num_rows($result);
            if(isset($_GET['start'])) {
                $start = $_GET['start'];
                if(isset($_GET['limit'])){
                    $limit = $_GET['limit'];
                }
                $totalPages = ceil($totalItems / $limit);
                if ($start < 1) {
                    $start = 1;
                }
                else if ($start > $totalPages) {
                    $start = $totalPages;
                }
                $offset = ($start * $limit) - $limit;

                $query = "SELECT * FROM Boekencollectie LIMIT " . $limit . " OFFSET " . $offset;
                $result = mysqli_query($db, $query) or die(mysqli_error($db));
            }
            else if(isset($_GET['limit'])) {
                $limit = $_GET['limit'];
                $start = 1;
                $totalPages = ceil($totalItems / $limit);

                $query = "SELECT * FROM Boekencollectie LIMIT " . $limit;
                $result = mysqli_query($db, $query) or die(mysqli_error($db));
            }
            else
            {
                $query = "SELECT * FROM Boekencollectie";
                $result = mysqli_query($db, $query) or die(mysqli_error($db));
                $start = 1;
                $totalPages = 1;
                $limit = $totalItems;
            }

            if(mysqli_num_rows($result)) {
                while($item = mysqli_fetch_assoc($result)) {
                    $id = $item['id'];
                    $links = array(
                        'links' => array(
                            array('rel' => 'self',
                                  'href' => $url.$id),
                            array('rel' => 'collection',
                                  'href' => $url)
                        )
                    );

                    $items[] = $item+$links;
                }
            }
            if($start == 1){
                $prevPage = 1;
            }
            else
            {
                $prevPage = $start - 1;
            }
            if($start < $totalPages)
            {
                $nextPage = $start + 1;
            }
            else
            {
                $nextPage = $totalPages;
            }

        $channellink = array(
            array(
                'rel' => 'self',
                'href' => "$url"."$detailId")
        );

        $linkspagi = array(
            array(
                "rel" => "first",
                "page" => $pagenr,
                "href" => $url . "?start=" . $pagenr . "&limit=" . $limit),
            array("rel" => "last",
                "page" => $totalPages,
                "href" => $url . "?start=" . $totalPages . "&limit=" . $limit),
            array(
                "rel" => "previous",
                "page" => $prevPage,
                "href" => $url . "?start=" . $prevPage . "&limit=" . $limit),
            array("rel" => "next",
                "page" => $nextPage,
                "href" => $url . "?start=" . $nextPage . "&limit=" . $limit));

        $channelpagi = array(
            'currentPage' => $start,
            'currentItems' => $limit,
            'totalPages' => $totalPages,
            'totalItems' => $totalItems,
            'links' => $linkspagi
        );
        }
        if($accept == 'application/json'){
            header("Content-Type: application/json");
            if(isset ($_GET['id'])){
                echo json_encode($items);
            }
            else
            {
                echo json_encode(
                    array(
                        'items' => $items)
                    +array(
                        'links' => $channellink)
                    +array(
                        'pagination' => $channelpagi)
                );
            }
        }
        else if($accept == 'application/xml'){
            header("Content-Type: application/xml");
            $xml = new SimpleXMLElement('<?xml version="1.0"?><items></items>');
            array_to_xml($items,$xml);
            $channel =  $xml->asXML();
            echo $channel;
        }
        else
        {
            http_response_code(405);
        }
        break;


    case "POST":
        $content = $_SERVER["CONTENT_TYPE"];
        if (!isset($id)){
            if ($content == "application/json"){
                //http_response_code(201);

                $body = file_get_contents("php://input");
                $json = json_decode($body);
                header("Content-Type: application/json");

                if(!empty($json)){

                    if($json->name !== "" && $json->author !== "" && $json->genre !== ""  && $json->pages !== ""){

                        $name = $json->name;
                        $author = $json->author;
                        $genre = $json->genre;
                        $pages = $json->pages;

                        $query = "INSERT INTO Boekencollectie (name, author, genre, pages) VALUES ('$json->name','$json->author','$json->genre','$json->pages')";
                        $result = mysqli_query($db, $query) or die(mysqli_error($db));

                        http_response_code(201);
                    }
                    else
                    {
                        http_response_code(400);
                    }
                }
                else
                {
                    http_response_code(405);
                }
            }

            else if ($content == "application/x-www-form-urlencoded"){
                header("Content-Type: application/x-www-form-urlencoded");

                    if(!empty($_POST['name']) && ($_POST['author']) && ($_POST['genre']) && ($_POST['pages'])){
                        $name = $_POST["name"];
                        $author = $_POST["author"];
                        $genre = $_POST["genre"];
                        $pages = $_POST["pages"];

                        $query = "INSERT INTO Boekencollectie (name, author, genre, pages) VALUES ('$name','$author','$genre','$pages')";
                        $result = mysqli_query($db, $query) or die(mysqli_error($db));
                        http_response_code(201);
                    }
                    else {
                        http_response_code(400);
                    }
            }
            else {
                http_response_code(415);
            }
        }
        else {
            http_response_code(405);
        }
        break;


    case "DELETE":
        if($_GET['id']){
            $id = $_GET['id'];
            header('Content-Type: application/json');
            $body = file_get_contents("php://input");
            $json = json_decode($body);
            $query = "DELETE FROM Boekencollectie WHERE id ='$id'";
            $result = mysqli_query($db, $query) or die(mysqli_error($db));
            http_response_code(204);
        }
        else
        {
            http_response_code(405);
        }

        break;

    case "PUT":
        $content = $_SERVER["CONTENT_TYPE"];
        if(isset($id)){
            if ($content == "application/json"){
                header('Content-Type: application/json');
                $body = file_get_contents("php://input");
                $json = json_decode($body);
                print_r($json);

                if($json->name !== "" || $json->author !== "" || $json->genre !== ""  || $json->pages !== ""){

                    $query = "UPDATE Boekencollectie SET name = '$json->name', author = '$json->author', genre = '$json->genre', pages = '$json->pages' WHERE id =".$id;
                    $result = mysqli_query($db, $query) or die(mysqli_error($db));

                    http_response_code(200);
                }
                else {
                    http_response_code(400);
                }
            }
            else{
                http_response_code(405);
            }
        }

        break;

        case "OPTIONS":
            if(isset($id)){
                header('ALLOW: GET, PUT, DELETE, OPTIONS');
            }
            else{
                header('ALLOW: GET, POST, OPTIONS');
            }
            break;

    default:
        http_response_code(405);
}

function array_to_xml ($data, $xml){
    foreach($data as $key => $value){
        if(is_array($value)){
            if(is_numeric($key)){
                $key = "item" . $key;
            }
            $child = $xml->addChild($key);
            array_to_xml($value,$child);
        }
        else
        {
            $xml->addChild("$key",htmlspecialchars($value));
        }
    }
}

?>

