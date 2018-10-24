<ul class="sidebar navbar-nav">
  <li class="nav-item <?php if(Request::is('dashboard')) { ?> active <?php } ?>">
    <a class="nav-link" href="dashboard">
      <i class="fas fa-fw fa-tachometer-alt"></i>
      <span>Dashboard</span>
    </a>
  </li>  
  <?php  	  	
  	asort($actors);
  	foreach($actors as $actor)
	{
		?>
		<li class="nav-item <?php if(Request::is($actor)) { ?> active <?php } ?>">
		    <a class="nav-link" href="<?php echo $actor; ?>">
		      <i class="fas fa-fw fa-building"></i>
		      <span><?php echo strtoupper($actor); ?></span>
		    </a>
	    </li>
		<?php
	}
  ?>   
</ul>