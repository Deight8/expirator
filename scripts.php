<!-- scripts.php -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!--   Core JS Files   --> 
<script src="./assets/js/core/popper.min.js" ></script> 
<script src="./assets/js/plugins/perfect-scrollbar.min.js" ></script> 
<script src="./assets/js/plugins/smooth-scrollbar.min.js" ></script> 
<script>
  var win = navigator.platform.indexOf('Win') > -1;
  if (win && document.querySelector('#sidenav-scrollbar')) {
    var options = {
      damping: '0.5'
    }
    Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
  }
</script>