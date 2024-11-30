<!-- Navbar -->
<nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 mt-3 shadow-none border-radius-xl" id="navbarBlur" data-scroll="true">
  <div class="container-fluid py-1 px-3">
    <?php if (!empty($success_message)): ?>
    <div class="alert alert-success alert-dismissible out-animation fade show m-0 pt-2 pb-1" role="alert"> <?php echo htmlspecialchars($success_message); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>
    <?php if (isset($error_message)): ?>
    <div class="alert alert-danger alert-dismissible out-animation fade show" role="alert"> <?php echo htmlspecialchars($error_message); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>
    <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4 d-flex justify-content-end" id="navbar">
      <ul class="navbar-nav  justify-content-end">
        <li class="nav-item d-flex align-items-center mx-3"> <a href="./ucet.php" class="nav-link text-body font-weight-bold px-0" title="Zobrazit účet"> <i class="fa fa-user me-sm-1"></i> <span class="d-sm-inline d-none">Můj účet</span> </a> </li>
        <li class="nav-item d-flex align-items-center"> <a href="./logout.php" class="nav-link text-body font-weight-bold px-0" title="Odhlásit"> <i class="fa-solid fa-power-off me-sm-1"></i> </a> </li>
      </ul>
    </div>
  </div>
</nav>
<!-- End Navbar -->