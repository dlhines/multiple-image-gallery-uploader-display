<?php
$html = '
<!--- Multiple Image Gallery Uploader Display (MIGUD)---!>
<div id="migud-render" class="clearfix">
  <h5>TITLE-OF-YOUR-GALLERY</h5>
  <a class="migud-render-link" data-fancybox="gallery" href="LINK-TO-YOUR-IMAGE">
  	<img class="migud-render-link-image" src="SOURCE-TO-YOUR-IMAGE" alt="ALTERNATIVE-TO-YOUR-IMAGE" />
  </a>
  ...repeat for each img...
</div>
<!--- End (MIGUD)---!>
';
?>
<style>
  .display_instructions .di_code{
    background: #FFF;
    color: #000;
    border: 1px solid #000;
    margin-left: auto;
    margin-right: auto;
    padding-left: 20px;
    padding-right: 20px;
    width: 70%;
  }
  .display_instructions hr {
    margin-top: 15px;
  }
</style>
<div class="admin-miud display_instructions clearfix">
  <h4>The shortcode is: </h4>
  <div class="di_code">
    <p>
      [miud id="THE PAGE ID" title="TITLE OF YOUR GALLERY"]
    </p>
  </div>
  <p>This shortcode is provided in the "Multiple Image Gallery Uploader Display (MIGUD)" configuration meta box for each Post Type you use with this plugin. It will automatically generate the id attribute for you. All you will have to do is supply a title (if needed), then copy paste the shortcode into that particular Post Type content window.</p>
  <hr />
  <h4>The Shortcode provided by this plugin produces the following html:</h4>
  <div class="di_code">
    <pre>
      <?php echo htmlentities($html); ?>
    </pre>
  </div>
  <hr />
  <h4>ID's and CLASS attributes:</h4>
  <h5>ID's</h5>
  <p>
    <b>migud-render</b>:<br />
    The only ID used to render output. It encloses all the generated <b>a</b> links and <b>img</b> links.
  </p>
  <h5>CLASSes</h5>
  <p><b>migud-render-link</b><br />
    This class can be used to manipulate each <b>a</b> html tag with either CSS or Javascript. There is nothing assigned to it as default. The best way to do this is to use a tag like below:<br >
    <div class="di_code"><p>#migud-render .migud-render-link</p></div>
  </p>
  <p><b>migud-render-link-image</b><br />
    This class can be used to manipulate each <b>a</b> html tag with either CSS or Javascript. There is nothing assigned to it as default. The best way to do this is to use a tag like below:<br >
    <div class="di_code"><p>#migud-render .migud-render-link .migud-render-link-image</p></div>
  </p>
</div>
