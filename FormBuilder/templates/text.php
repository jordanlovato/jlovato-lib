<?php
/**
 * Created by Jordan Lovato.
 * This file is used by the FormBuilder class as a template for fields with type='text'
 */
?>
<?php include('before.php'); ?>
<label for="<?php echo $id; ?>"><?php echo $label; ?></label>
<input type="text" class="<?php echo $class; ?>" name="<?php echo $name; ?>" id="<?php echo $id; ?>"
       class="<?php echo $class; ?>" value="<?php echo $default; ?>" />
<?php include('after.php'); ?>