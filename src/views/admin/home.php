<?php 

require __DIR__ . '/header.php'; 
require __DIR__ . '/../mysqli.php';
require __DIR__ . '/../../csrf.php';
require __DIR__ . '/util.php';
require __DIR__ . '/../abuseipdb.php';

if(isset($_POST['export']) && CSRF::validateToken(filter_input(INPUT_POST, 'token', FILTER_UNSAFE_RAW)) && !AbuseIPDB::Listed($_SERVER['REMOTE_ADDR'], 50)) {
	(isset($_POST['compression']))?$compression = true:$compression=false;
    exportDB($compression, $mysqli);
}

if(isset($_POST['import']) && CSRF::validateToken(filter_input(INPUT_POST, 'token', FILTER_UNSAFE_RAW)) && !AbuseIPDB::Listed($_SERVER['REMOTE_ADDR'], 50)) {
	if(isset($_FILES['sql']) && $_FILES['sql']['error'] == 0 && !clamdscan($_FILES['sql']['tmp_name'])){
		importDB($_FILES['sql']['tmp_name'], $mysqli);
	}
}

if(isset($_POST['send-email']) && CSRF::validateToken(filter_input(INPUT_POST, 'token', FILTER_UNSAFE_RAW)) && !AbuseIPDB::Listed($_SERVER['REMOTE_ADDR'], 50)) {
    $title = filter_input(INPUT_POST, 'title');
    $message = filter_input(INPUT_POST, 'message');
	if(isset($_FILES['attachment']) && $_FILES["attachment"]["error"] == 0) {
		$attachment['name'] = $_FILES['attachment']['name'];
		$attachment['tmp_name'] = $_FILES['attachment']['tmp_name'];
	} else {
		$attachment = null;
	}
    if($_POST['flexRadioDefault'] == 'all') {
        $emails = array();
        $statement = $mysqli->prepare("SELECT * FROM users");
        $statement->execute();
        $result = $statement->get_result();
		if($statement->affected_rows < 0){
			while($data = $result->fetch_assoc()){
				array_push($results, $data);
			}
		}
        foreach($results as $data) {
            $emails[] = $data['email'];
        }
        sendEmail($emails, $title, $message, false, $attachment, null);
    } else {
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        sendEmail(array($email), $title, $message, false, $attachment, null);
    }
}

$dateRange0 = date('Y-m-d') . ' 00:00:00 GMT';
$dateRange1 = date('Y-m-d') . ' 22:59:59 GMT';
$result = $mysqli->query("SELECT count(*) FROM transactions WHERE timestamp >= '$dateRange0' AND timestamp <= '$dateRange1'");
$orderCount = $result->fetch_assoc()["count(*)"];
$revenue = 0;

$transactions = $mysqli->query("SELECT * FROM transactions WHERE timestamp >= '$dateRange0' AND timestamp <= '$dateRange1'");
foreach($transactions as $transaction) {
    $details = unserialize($transaction['details']);
    foreach($details as $detail) {
        $revenue += $detail['price'] * $detail['quantity'];
    }
}

$result = $mysqli->query("SELECT count(*) FROM users");
$userCount = $result->fetch_assoc()['count(*)'];

$csrf = CSRF::csrfInputField();
?>
<div class="container">
    <div class="row">
        <div class="col-md-12 page-header">
            <div class="page-pretitle">Overview</div>
            <h2 class="page-title">Home</h2>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-6 col-md-6 col-lg-4 mt-3">
            <div class="card">
                <div class="content">
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="icon-big text-center">
                                <i class="teal fas fa-shopping-cart"></i>
                            </div>
                        </div>
                        <div class="col-sm-8">
                            <div class="detail">
                                <p class="detail-subtitle">Orders</p>
                                <span class="number"><?= $orderCount ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="footer">
                        <hr />
                        <div class="stats">
                            <i class="fas fa-calendar"></i> For Today
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-6 col-lg-4 mt-3">
            <div class="card">
                <div class="content">
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="icon-big text-center">
                                <i class="olive fas fa-money-bill-alt"></i>
                            </div>
                        </div>
                        <div class="col-sm-8">
                            <div class="detail">
                                <p class="detail-subtitle">Revenue</p>
                                <span class="number">$ <?= number_format($revenue, 2) ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="footer">
                        <hr />
                        <div class="stats">
                            <i class="fas fa-calendar"></i> For Today
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-6 col-lg-4 mt-3">
            <div class="card">
                <div class="content">
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="icon-big text-center">
                                <i class="grey fas fa-users"></i>
                            </div>
                        </div>
                        <div class="col-sm-8">
                            <div class="detail">
                                <p class="detail-subtitle">Users</p>
                                <span class="number"><?= $userCount ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="footer">
                        <hr />
                        <div class="stats">
                            <i class="fas fa-calendar"></i> All
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="content">
                            <div class="head">
                                <h5 class="mb-0">Orders Overview</h5>
                                <p class="text-muted">Orders in the last 7 days</p>
                            </div>
                            <div class="canvas-wrapper">
                                <canvas class="chart" id="orders"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="content">
                            <div class="head">
                                <h5 class="mb-0">Revenue Overview</h5>
                                <p class="text-muted">Revenue in the last 7 days</p>
                            </div>
                            <div class="canvas-wrapper">
                                <canvas class="chart" id="revenue"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"></h5>
                    <div class="mb-3 row">
                        <label class="col-sm-2">Send Email <br>
                            <!-- <small class="text-info">Normal Bootstrap elements</small> -->
                        </label>
                        <form action="/admin/home" method="post" enctype="multipart/form-data">
                        <?= $csrf ?>
                        <div class="col-sm-10">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="flexRadioDefault" id="radio1" value="all">
                                <label class="form-check-label" for="radio1">All customers</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="flexRadioDefault" id="radio2" checked value="specify">
                                <label class="form-check-label" for="radio2">Specify Email</label>
                            </div>
                            <div class="form-check" id="email-field">
                                <input type="email" name="email" placeholder="Email" class="form-control">
                            </div><br>
                            <div class="form-check">
                                <input type="text" name="title" placeholder="Title" class="form-control">
                            </div><br>
                            <div class="form-check">
                                <label class="form-label">Attachment</label>
                                <input class="form-control" name="attachment" type="file" id="formFile1">
                                <small class="text-muted"></small>
                            </div><br>
                            <div class="form-check">
                                <textarea style="resize:none" type="text" name="message" placeholder="Message..." class="form-control" rows="3"></textarea>
                            </div><br>
                            <div class="form-check">
                                <button type="submit" name="send-email" class="btn btn-primary col-sm-4">Send</button>
                            </div>
                        </form>
                        </div>
                    </div>
                    <div class="line"></div><br>
                    <div class="mb-3 row">
                        <label class="col-sm-2">Database:</label>
                        <div class="col-sm-10">
                            <div class="mb-3 row">
                                <div class="col-sm-12">
                                    <div class="row">
                                        <form action="/admin/home" id="import-form" method="post" enctype="multipart/form-data">
                                            <div class="col-sm-1">
                                                <?= $csrf ?>
                                                <input type="file" name="sql" id="file" required>
                                            </div><br>
                                            <div class="col-sm-12">
                                                <button name="import" type="submit" class="btn btn-secondary mb-2"><i class="fas fa-file-import"></i> Import</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="col-sm-12">
                                    <form action="/admin/home" method="post">
                                        <?= $csrf ?>
                                        <button name="export" type="submit" class="btn btn-primary mb-2"><i class="fas fa-file-export"></i> Export</button>
										<label class="form-label">Compression?</label>
										<input type="checkbox" name="compression" value="true">
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/footer.php'; ?>