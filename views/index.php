<div class="wrap">
  <h1><?php echo $plugin_title ?></h1>
  <p>Use this manager to update and create or delete entries.</p>
  <?php if(!empty($message)): ?>
    <div id="message"><p><em><?php echo $message ?></em></p></div>
  <?php endif; ?>
  <table class="wp-list-table widefat fixed users" cellspacing="0">
    <thead>
      <th>Title</th>
      <th>Description</th>
      <th></th>
      <th></th>
    </thead>
    <tbody id="the-list" data-wp-lists="list:user">
      <?php foreach ($entries as $entry): ?>
        <tr id="item-<?php echo $entry['id'] ?>">
          <td><?php echo $entry['title'] ?></td>
          <td><?php echo $entry['description'] ?></td>
          <td><a href="<?php echo $route_url; ?>edit=<?php echo $entry['id'] ?>">Edit</a></td>
          <td><a href="<?php echo $route_url; ?>delete=<?php echo $entry['id'] ?>">Delete</a></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <hr>
  <a class="button button-primary" href="<?php echo $route_url; ?>new">Create New</a>
</div>
<script>
  jQuery(document).ready(function($) {
    function getOrder() {
      var items = $('tbody').find('tr');
      return $.map(items, function(item, index) {
        return item.id;
      });
    }
    nativesortable(document.getElementById("the-list"), {
      change: function() {
        $.post("<?php echo $site_url; ?>/wp-admin/admin-ajax.php", {
          'order': getOrder(),
          action : "<?php echo $plugin_ajax_action ?>"
        }).then(function(res) {
          // console.log(res);
        }, function(err) {
          console.error(err);
        });
      }
    });
  });
</script>