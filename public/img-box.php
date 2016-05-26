<?

$author = '';
$callnumber = '';
$title = '';
$isbn = '';
$oclc = '';
$uid = $_GET["uid"];

?>

<html>
<body>
    <center>

        <img src="/Cover/Show?author=<?=$author?>&callnumber=<?=$callnumber?>&size=small&title=<?=$title?>&isbn=<?=$isbn?>&oclc=<?=$oclc?>&uid=<?=$uid?>">

    </center>
</body>
</html>

