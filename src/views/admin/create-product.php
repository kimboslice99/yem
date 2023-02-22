<?php

require __DIR__ . '/header.php';
require __DIR__ . '/../mysqli.php';
require __DIR__ . '/../../csrf.php';
require __DIR__ . '/util.php';
require __DIR__ . '/../abuseipdb.php';

if(isset($_POST['submit']) && CSRF::validateToken(filter_input(INPUT_POST, 'token', FILTER_UNSAFE_RAW)) && !AbuseIPDB::Listed($_SERVER['REMOTE_ADDR'], 50)) {
    $title = filter_input(INPUT_POST, 'title');
    $description = filter_input(INPUT_POST, 'description');
    $price = filter_input(INPUT_POST, 'price');
    $category = filter_input(INPUT_POST, 'category');
    $qty = filter_input(INPUT_POST, 'qty', FILTER_SANITIZE_NUMBER_INT);
    $weight = filter_input(INPUT_POST, 'w', FILTER_SANITIZE_NUMBER_INT);
    $statement = $mysqli->prepare("SELECT count(*) FROM categories WHERE title=?");
	$statement->bind_param('s', $category);
    $statement->execute();
	$statement->get_result();
    if(!$statement->affected_rows > 0) {
        $statement = $mysqli->prepare("INSERT INTO categories(title) VALUES (?)");
		$statement->bind_param('s', $category);
        $statement->execute();
    }
    $paths = serialize(uploadImages());
    $statement = $mysqli->prepare("INSERT INTO products(title, price, description, category, images, qty, weight) VALUES (?, ?, ?, ?, ?, ?, ?)");
	$statement->bind_param('sdsssii', $title, $price, $description, $category, $paths, $qty, $weight);
    $statement->execute();
    header('Location: /admin/products');
}
$categories = array();
$statement = $mysqli->prepare("SELECT * FROM categories");
$statement->execute();
$result = $statement->get_result();
if($statement->affected_rows > 0){
	while($assoc = $result->fetch_assoc()){
		array_push($categories, $assoc);
	}
}
?>
<div class="container">
    <div class="row">
        <div class="card">
            <div class="card-header">Create Product</div>
            <div class="card-body">
                <div class="col-md-6">
                    <form action="/admin/products/create" method="post" enctype="multipart/form-data">
                        <?= CSRF::csrfInputField() ?>
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Price</label>
                            <input type="number" name="price" class="form-control" min=0 step=0.01 required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" style="resize:none" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Quantity</label>
                            <input class="form-control" name="qty" type="number" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Weight</label>
                            <input class="form-control" name="w" type="number" required>
                        </div>
                        <div class="mb-3">
                            <label for="language" class="form-label">Category</label>
                             <div class="input-group mb3">
                        	    <div class="dropdown input-group-prepend">
                            	  <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                            		Choose
                            	  </button>
                            	  <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                            		<?php foreach($categories as $category): ?>
                            		    <li><a class="dropdown-item"><?= $category['title'] ?></a></li>
                            		    <div role="separator" class="dropdown-divider"></div>
                            		<?php endforeach; ?>
                            	  </ul>
                        	    </div>
                            	<input id="category" type="text" name="category" class="form-control" aria-label="Text input with dropdown button" value="">
                        	</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Images</label>
                            <input class="form-control" name="files[]" type="file" id="formFile1" multiple>
                            <small class="text-muted">Select product images</small>
                        </div>
                        <div class="mb-3 text-end">
                            <button name="submit" type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div> 
    </div>
</div>

<?php require __DIR__ . '/footer.php'; ?>
<script>
    $('.dropdown-item').click(function() {
        $('#category').val($(this).text())
    })
</script>
<?php
//var_dump($items[0]['category']);
?>