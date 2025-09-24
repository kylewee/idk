
<ul class="nav nav-tabs">
  <li class="active"><a href="#default_icons"  data-toggle="tab"><?php echo TEXT_DEFAULT ?></a></li>
  <li><a href="#extra_icons"  data-toggle="tab"><?php echo TEXT_EXTRA ?></a></li>
</ul>
 
<div class="tab-content">
    <div class="tab-pane fade active in" id="default_icons">
        <?php require(component_path('fontawesome/fontawesome')) ?>
    </div>
    <div class="tab-pane fade" id="extra_icons">
        <?php require(component_path('fontawesome/lineawesome')) ?>
    </div>
</div>