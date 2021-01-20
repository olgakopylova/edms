<?php
require_once($modules_root."edms/class/DocumentationHome.class.php");
if ($request->hasValue('doc_files')) {
    $filename = $request->getValue('file');
    $file = realpath('files/lk/docs/documentation') . '/' . $filename;
    download($file);
}
if($request->hasValue('doc_sign')){
    $id=$request->getValue('id_doc');
    $file=DocumentationHome::generateSign($id);
    download($file);
}

function download($file){
    if (file_exists($file)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($file) . '"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        ob_clean();
        readfile($file, true);
        die();
    } else {
        die($file);
    }
}