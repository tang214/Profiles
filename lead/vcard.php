<?php
header('Content-Type: text/x-vCard');
$json = $_GET['data'];
$data = json_decode($json);
$name = $data->legalName;
$space_pos = strpos($name, ' ');
$first = substr($name, 0, $space_pos);
$last = substr($name, $space_pos);
$title = $data->title;
$phone = $data->phone;
$email = $data->email;
header('Content-Disposition: attachment; filename="'.$name.'.vcf"');
?>
BEGIN:VCARD
VERSION:2.1
N:<?php echo $last.';'.$first."\n" ?>
FN: <?php echo $name."\n" ?>
ORG: Austin Artistic Reconstruction
TITLE: <?php echo $title."\n" ?>
TEL;TYPE=MOBILE,VOICE:<?php echo $phone."\n" ?>
EMAIL;TYPE=PREF,INTERNET:<?php echo $email."\n" ?>
END:VCARD
