<?php

function uploadImage($file, $targetDir = '../uploads/dishes/') {
    $errors = [];
    $filename = '';
    
    
    if ($file['error'] === UPLOAD_ERR_OK) {
        
        $maxSize = 5 * 1024 * 1024; 
        if ($file['size'] > $maxSize) {
            $errors[] = "L'image est trop volumineuse (max 5MB).";
        }
        
        
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $allowedTypes)) {
            $errors[] = "Type de fichier non autorisé. Formats acceptés : JPG, PNG, GIF, WebP.";
        }
        
        
        if (empty($errors)) {
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }
            
           
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid('dish_', true) . '.' . $extension;
            $filepath = $targetDir . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                
                compressImage($filepath, 800, 600);
            } else {
                $errors[] = "Erreur lors du téléchargement de l'image.";
            }
        }
    } elseif ($file['error'] !== UPLOAD_ERR_NO_FILE) {
        $errors[] = "Erreur lors du téléchargement : code " . $file['error'];
    }
    
    return [
        'success' => empty($errors),
        'filename' => $filename,
        'errors' => $errors
    ];
}


function compressImage($source, $maxWidth = 800, $maxHeight = 600) {
    $info = getimagesize($source);
    
    if ($info === false) {
        return false;
    }
    
    list($width, $height, $type) = $info;
    
    
    $ratio = min($maxWidth / $width, $maxHeight / $height);
    $newWidth = (int)($width * $ratio);
    $newHeight = (int)($height * $ratio);
    
    
    switch ($type) {
        case IMAGETYPE_JPEG:
            $image = imagecreatefromjpeg($source);
            break;
        case IMAGETYPE_PNG:
            $image = imagecreatefrompng($source);
            break;
        case IMAGETYPE_GIF:
            $image = imagecreatefromgif($source);
            break;
        default:
            return false;
    }
    
    $newImage = imagecreatetruecolor($newWidth, $newHeight);
    
   
    if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_GIF) {
        imagecolortransparent($newImage, imagecolorallocatealpha($newImage, 0, 0, 0, 127));
        imagealphablending($newImage, false);
        imagesavealpha($newImage, true);
    }
    
   
    imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    
    
    switch ($type) {
        case IMAGETYPE_JPEG:
            imagejpeg($newImage, $source, 85);
            break;
        case IMAGETYPE_PNG:
            imagepng($newImage, $source, 8);
            break;
        case IMAGETYPE_GIF:
            imagegif($newImage, $source);
            break;
    }
    
    
    imagedestroy($image);
    imagedestroy($newImage);
    
    return true;
}


function formatPrice($price) {
    return number_format($price, 2, ',', ' ') . ' €';
}


function getRandomColorClass() {
    $colors = ['primary', 'success', 'info', 'warning', 'danger', 'secondary'];
    return $colors[array_rand($colors)];
}

function truncateText($text, $length = 100) {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . '...';
}
?>