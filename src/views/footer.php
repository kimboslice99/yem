<?php

$fb = $config['contact_facebook'];
$tw = $config['contact_twitter'];
$ig = $config['contact_instagram'];
?>
    <footer class="footer section text-center">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <ul class="social-media">
					  <?php if(!empty($fb)): ?>
                        <li>
                            <a href="https://www.facebook.com/<?= $fb ?>">
                                <i class="tf-ion-social-facebook"></i>
                            </a>
                        </li>
					  <?php endif ?>
					  <?php if(!empty($ig)): ?>
                        <li>
                            <a href="https://www.instagram.com/<?= $ig ?>">
                                <i class="tf-ion-social-instagram"></i>
                            </a>
                        </li>
					  <?php endif ?>
					  <?php if(!empty($tw)): ?>
                        <li>
                            <a href="https://www.twitter.com/<?= $tw ?>">
                                <i class="tf-ion-social-twitter"></i>
                            </a>
                        </li>
					  <?php endif ?>
                    </ul>
                    <ul class="footer-menu text-uppercase">
                        <li>
                            <a href="contact">CONTACT</a>
                        </li>
                        <li>
                            <a href="/products">SHOP</a>
                        </li>
                        <li>
                            <a href="/privacy-policy">PRIVACY POLICY</a>
                        </li>
                        <li>
                            <a href="/faq">FAQ</a>
                        </li>
                    </ul>
                    <p class="copyright-text">Copyright &copy;<?= date('Y') ?><br></p><p class="font-10"><br>Hello, <?= $_SERVER['REMOTE_ADDR']; ?></p>
                </div>
            </div>
        </div>
    </footer>

   
   <!-- 
    Essential Scripts
    =====================================-->
    
    <!-- Main jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.3.min.js" integrity="sha256-pvPw+upLPUjgMXY0G+8O0xUf+/Im1MZjXxxgOcBQBXU=" crossorigin="anonymous"></script>
    <!-- Popper 2.11.6 -->
	<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
    <!-- Bootstrap 5.3.0 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js" integrity="sha384-mQ93GR66B00ZXjt0YO5KlohRA5SY2XofN4zfuZxLkoj1gXtW8ANNCe9d5Y3eG5eD" crossorigin="anonymous"></script>
    <!-- bs5 Lightbox Plugin v1.8.3 -->
	<script src="/plugins/lightbox/index.bundle.min.js"></script>
    <!-- Count Down Js -->
    <script src="/plugins/syo-timer/build/jquery.syotimer.min.js"></script>

    <!-- slick Carousel - Where was this ever used? odd
    <script src="/plugins/slick/slick.min.js"></script>
    <script src="/plugins/slick/slick-animation.min.js"></script> -->

    <!-- Main Js File -->
    <script src="/js/script.js"></script>
    <?php if(isset($_SESSION['name'])): ?>
        <!--<script>
            window.intercomSettings = {
                api_base: "https://api-iam.intercom.io",
                app_id: "",
                name: "<?= $_SESSION['name'] ?>", // Full name
                email: "<?= $_SESSION['email'] ?>", // Email address
                created_at: "<?= $_SESSION['created-time'] ?>" // Signup date as a Unix timestamp
            };
        </script>

        <script>
            
            (function(){var w=window;var ic=w.Intercom;if(typeof ic==="function"){ic('reattach_activator');ic('update',w.intercomSettings);}else{var d=document;var i=function(){i.c(arguments);};i.q=[];i.c=function(args){i.q.push(args);};w.Intercom=i;var l=function(){var s=d.createElement('script');s.type='text/javascript';s.async=true;s.src='https://widget.intercom.io/widget/qq33os1d';var x=d.getElementsByTagName('script')[0];x.parentNode.insertBefore(s,x);};if(document.readyState==='complete'){l();}else if(w.attachEvent){w.attachEvent('onload',l);}else{w.addEventListener('load',l,false);}}})();
        </script>-->
    <?php else: ?>
        <!--<script>
            window.intercomSettings = {
                api_base: "https://api-iam.intercom.io",
                app_id: ""
            };
        </script>-->

        <!--<script>
            
            (function(){var w=window;var ic=w.Intercom;if(typeof ic==="function"){ic('reattach_activator');ic('update',w.intercomSettings);}else{var d=document;var i=function(){i.c(arguments);};i.q=[];i.c=function(args){i.q.push(args);};w.Intercom=i;var l=function(){var s=d.createElement('script');s.type='text/javascript';s.async=true;s.src='https://widget.intercom.io/widget/qq33os1d';var x=d.getElementsByTagName('script')[0];x.parentNode.insertBefore(s,x);};if(document.readyState==='complete'){l();}else if(w.attachEvent){w.attachEvent('onload',l);}else{w.addEventListener('load',l,false);}}})();
        </script>-->
    <?php endif ?>

  </body>
  </html>
