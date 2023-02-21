<?php 

require __DIR__ . '/header.php'; 
require __DIR__ . '/../db.php';
require __DIR__ . '/../../csrf.php';
require __DIR__ . '/util.php';
require __DIR__ . '/../abuseipdb.php';

$items;
$categories;
$edit = false;

// Save
if(isset($_POST['submit']) && CSRF::validateToken(filter_input(INPUT_POST, 'token', FILTER_UNSAFE_RAW)) && !AbuseIPDB::Listed($_SERVER['REMOTE_ADDR'], 50)) {
    $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
    if(isset($_POST['title'])) {
        $statement = $pdo->prepare("UPDATE products SET title=? WHERE id=?");
        $statement->execute(array(filter_input(INPUT_POST, 'title'), $id));
    }
    if(isset($_POST['price'])) {
        $statement = $pdo->prepare("UPDATE products SET price=? WHERE id=?");
        $statement->execute(array(filter_input(INPUT_POST, 'price'), $id));
    }
    if(isset($_POST['description'])) {
        $statement = $pdo->prepare("UPDATE products SET description=? WHERE id=?");
        $statement->execute(array(filter_input(INPUT_POST, 'description'), $id));
    }
    if(isset($_POST['qty'])) {
        $statement = $pdo->prepare("UPDATE products SET qty=? WHERE id=?");
        $statement->execute(array(filter_input(INPUT_POST, 'qty', FILTER_SANITIZE_NUMBER_INT), $id));
    }
    if(isset($_POST['category'])) {
        $statement = $pdo->prepare("SELECT * FROM categories WHERE title=?");
        $statement->execute(array(filter_input(INPUT_POST, 'category')));
        if(!$statement->rowCount() > 0) {
            $statement = $pdo->prepare("INSERT INTO categories(title) VALUES (?)");
            $statement->execute(array(filter_input(INPUT_POST, 'category')));
        }
        $statement = $pdo->prepare("UPDATE products SET category=? WHERE id=?");
        $statement->execute(array(filter_input(INPUT_POST, 'category'), $id));
    }
    if(isset($_FILES['files'])) {
		
		$statement = $pdo->prepare("SELECT images FROM products WHERE id=?");
		$statement->execute(array($id));
		
		$images = uploadImages();
		// Add to existing images rather than replace
		if($statement->rowCount() > 0) {
		$result = $statement->fetchAll(PDO::FETCH_ASSOC);
			if (!empty($result[0]['images'])) {
				foreach(unserialize($result[0]['images']) as $pics) {
					array_push($images, $pics);
				}
			}
		}
        $path = serialize($images);
        $statement = $pdo->prepare("UPDATE products SET images=? WHERE id=?");
        $statement->execute(array($path, $id));
    }
}
// Delete picture
if(isset($_POST['delete_pic']) && isset($_POST['id']) && isset($_POST['image']) && CSRF::validateToken(filter_input(INPUT_POST, 'token', FILTER_UNSAFE_RAW))) {
    $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
    $image = filter_input(INPUT_POST, 'image');
	$statement = $pdo->prepare("SELECT images FROM products WHERE ID=?");
	$statement->execute(array($id));
		if($statement->rowCount() > 0) {
			 $result = $statement->fetchAll(PDO::FETCH_ASSOC);
			if (!empty($result[0]['images'])) {
				$array = unserialize($result[0]['images']);
				if (($key = array_search($image, $array)) !== false) { // Search the array
					unlink($array[$key]); //  Delete file
					unset($array[$key]); // Remove file from array
				}
			
			$statement = $pdo->prepare("UPDATE products SET images=? WHERE id=?");
			$statement->execute(array(serialize($array), $id));
			}
		}
}
$error = false;
// Open Product
if(isset($_GET['id'])) {
    $edit = true;
    $statement = $pdo->prepare("SELECT * FROM products WHERE id=?");
    $statement->execute(array(filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT)));
    if($statement->rowCount() > 0) {
        $items = $statement->fetchAll(PDO::FETCH_ASSOC);
    } else { $error = true; }
    $statement = $pdo->prepare("SELECT * FROM categories");
    $statement->execute();
    $categories = $statement->fetchAll(PDO::FETCH_ASSOC);
} else { // Delete product
    if(isset($_POST['delete']) && CSRF::validateToken(filter_input(INPUT_POST, 'token', FILTER_UNSAFE_RAW))) {
        $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
        $statement = $pdo->prepare("DELETE FROM products WHERE id=?");
        $statement->execute(array($id));
    }
    // List products
    $statement = $pdo->prepare("SELECT * FROM products");
    $statement->execute();
    if($statement->rowCount() > 0) {
        $items = $statement->fetchAll(PDO::FETCH_ASSOC);
    }
}
// List categories
$statement = $pdo->prepare("SELECT * FROM categories");
$statement->execute();
$categories = $statement->fetchAll(PDO::FETCH_ASSOC);
$csrf = CSRF::csrfInputField();
?>
<div class="container">
    <div class="page-title">
        <h3>Products
        <a href="/admin/products/create" class="btn btn-sm btn-outline-primary float-end"><i class="fas fa-plus"></i> Add</a>
        </h3>
    </div>
<?php if(!$error): ?>
    <?php if($edit): ?>
        <div class="card">
            <div class="card-header">Create Product</div>
            <div class="card-body">
                <div class="col-md-6">
                    <form action="/admin/products?id=<?= $_GET['id'] ?>" method="post" enctype="multipart/form-data">
                        <?= $csrf ?>
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" name="title" class="form-control" value="<?= $items[0]['title'] ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Price</label>
                            <input type="number" name="price" class="form-control" value="<?= $items[0]['price'] ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description"><?= $items[0]['description'] ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Quantity</label>
                            <input class="form-control" type="number" name="qty" value="<?= $items[0]['qty'] ?>">
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
                            	<input id="category" type="text" name="category" class="form-control" aria-label="Text input with dropdown button" value="<?= $items[0]['category'] ?>">
                        	</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Images</label>
                            <input class="form-control" name="files[]" type="file" id="formFile1" multiple>
                            <small class="text-muted">Select product images</small>
                        </div>
                        <div class="mb-3 text-end">
                            <input type="text" name="id" value="<?= $items[0]['id'] ?>" hidden>
                            <button name="submit" type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div> 
	
	<?php if(!empty($items[0]['images'])): ?>
	<div class="">
	<small class="text-muted">Current product images</small><br>
		<?php foreach(unserialize($items[0]['images']) as $image): ?>
		<form action="/admin/products?id=<?= $_GET['id'] ?>" method="post">
		<?= $csrf ?>
		<img class="w-25" src="/<?= $image ?>"/>
		<input name="image" value="<?= $image ?>" hidden>
		<input name="id" value="<?= $items[0]['id'] ?>" hidden>
		<button name="delete_pic" class="btn" type="submit">Delete</button>
		</form>
		<?php endforeach; ?>
	</div>
	<?php endif ?>
    <?php else: ?>
        <div class="box box-primary">
            <div class="box-body">
                <table width="100%" class="table table-hover" id="dataTables-example">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Price</th>
                            <th>Description</th>
                            <th>Quantity</th>
                            <th>Category</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($items)): ?>
                            <?php foreach($items as $item): ?>
                                <tr>
                                    <td><?= $item['title'] ?></td>
                                    <td>$<?= number_format($item['price'], 2) ?></td>
                                    <td><?= $item['description'] ?></td>
                                    <td><?= $item['qty'] ?></td>
                                    <td><?= $item['category'] ?></td>
                                    <td class="text-end">
                                        <form action="/admin/products?id=<?=  $items[0]['id'] ?>" method="post">
                                            <?= $csrf ?>
                                            <input type="text" name="id" value="<?= $item['id'] ?>" hidden>
                                            <a href="/admin/products?id=<?= $item['id']; ?>" class="btn btn-outline-info btn-rounded"><i class="fas fa-pen"></i></a>
                                            <button name="delete" type="submit" class="btn btn-outline-danger btn-rounded"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif ?>
</div>
<!-- Product ID missing -->
<!-- Product ID missing -->
<?php else: ?>

<div class="alert alert-danger text-center" role="alert">Error! Product ID missing!</div>

<?php endif ?>
<?php require __DIR__ . '/footer.php'; ?>
<script>
    $('.dropdown-item').click(function() {
        $('#category').val($(this).text())
    })
</script>