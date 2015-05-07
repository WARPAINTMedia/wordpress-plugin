<div class="wrap product-form">
  <h1><?php echo $plugin_title ?></h1>
  <p>Use this manager to update and create or delete entries.</p>
  <?php if(!empty($message)): ?>
    <div id="message"><p><em><?php echo $message ?></em></p></div>
  <?php endif; ?>
  <form action="<?php echo $route_url; ?>" method="post" enctype="multipart/form-data">
    <table class="form-table">
      <tbody>
        <tr>
          <td><input class="button button-primary" type="submit" value="Save"></td>
          <td><a class="button" href="<?php echo $route_url; ?>">Cancel</a></td>
        </tr>
        <tr>
          <th scope="row">
            <label for="title">Title</label>
          </th>
          <td>
            <input required type="text" id="title" name="title" maxlength="100" value="<?php echo $title ?>">
            <p class="description">The title for this entry</p>
          </td>
        </tr>
        <tr valign="top">
          <th scope="row">
            <label for="description">Description</label>
          </th>
          <td>
            <textarea id="description" name="description" rows="5" cols="102"><?php echo $description ?></textarea>
            <p class="description">Description for this entry</p>
          </td>
        </tr>
        <tr valign="top">
          <th scope="row">
            <label for="image">Image</label>
          </th>
          <td>
            <?php if(!is_null($request)): ?>
              <p><img src="<?php echo $image_url ?>" width="300" ></p>
              <input type="hidden" name="image_url" value="<?php echo $image_url ?>">
              <input type="hidden" name="image_file" value="<?php echo $image_file ?>">
            <?php endif; ?>
            <input type="file" id="image" name="image" accept="image/jpg" class="button button-primary">
            <p class="description">Entry Photo. JPG only. Maximum width of 300px.</p>
          </td>
        </tr>
        <tr>
          <input type="hidden" name="id" value="<?php echo $request ?>">
          <td><input class="button button-primary" type="submit" value="Save"></td>
          <td><a class="button" href="<?php echo $route_url; ?>">Cancel</a></td>
        </tr>
      </tbody>
    </table>
  </form>
</div>
<script>
var first_run = <?php echo ($request === NULL) ? 'true': 'false'; ?>;
</script>