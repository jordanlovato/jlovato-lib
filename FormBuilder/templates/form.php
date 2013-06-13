<?php
/**
 * Created by Jordan Lovato.
 */
?>

<form method="<?php echo $method; ?>" <?php if (!empty($action)) echo "action='$action'"; ?>>
    <?php
        foreach ($fields as $field) {
            extract($field);
            include($type.".php");
        }
    ?>
</form>