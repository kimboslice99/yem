<?php 

require __DIR__ . '/header.php';
require __DIR__ . '/db.php'; 

$statement = $pdo->prepare("SELECT * FROM about");
$statement->execute();
$about = $statement->fetchAll(PDO::FETCH_ASSOC);
?>
<section class="about section">
	<div class="container">
		<div class="row">
			<div class="col-md-6 mt-20">
				<img class="img-fluid" src="/images/logo.jpg">
			</div>
			<div class="col-md-6">
				<h2>About Our Shop</h2>
				<p><?= htmlspecialchars($about[0]['about']) ?></p>
			</div>
		</div>
		<div class="row mt-40">
            <div class="contact-details col-md-6 " >
                <ul class="contact-short-info" >
                    <li>
                        <i class="tf-ion-ios-home"></i>
                        <span><?= htmlspecialchars($config['address']) ?></span>
                    </li>
					<?php if(!empty($phone)): ?>
                    <li>
                        <i class="tf-ion-android-phone-portrait"></i>
                        <span>Phone: <?= htmlspecialchars($config['contact_phone']) ?></span>
                    </li>
					<?php endif ?>
                    <li>
                        <i class="tf-ion-android-mail"></i>
                        <span>Email: <?= htmlspecialchars($config['contact_email']) ?></span>
                    </li>
                </ul>
                <!-- Footer Social Links -->
                <div class="social-icon">
                    <ul>
                        <?php if(!empty($config['contact_facebook'])): ?><li><a class="fb-icon" href="https://facebook.com/<?= htmlspecialchars($config['contact_facebook']) ?>"><i class="tf-ion-social-facebook"></i></a></li><?php endif ?>
                        <?php if(!empty($config['contact_twitter'])): ?><li><a href="https://twitter.com/<?= htmlspecialchars($config['contact_twitter']) ?>"><i class="tf-ion-social-twitter"></i></a></li><?php endif ?>
                    </ul>
                </div>
                <!--/. End Footer Social Links -->
            </div>
		</div>
	</div>
</section>
<?php require __DIR__ . '/footer.php'; ?>