  <script src="<?= e(asset('js/icons.js')) ?>"></script>
  <?php foreach (($scripts ?? []) as $script): ?>
  <script src="<?= e(asset('js/' . $script)) ?>"></script>
  <?php endforeach; ?>
</body>
</html>
