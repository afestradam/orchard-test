<?php
// Agregar el menú de administración
function orchard_admin_menu()
{
    add_menu_page(
        'Orchard Products',  // Título de la página
        'Orchard Products',  // Texto del menú
        'manage_options',     // Permiso requerido
        'orchard-products',   // Slug
        'orchard_products_page', // Función que muestra la página
        'dashicons-cart',      // Ícono del menú
        6                     // Posición en el menú
    );
}

add_action('admin_menu', 'orchard_admin_menu');

// Eliminar producto si se hace clic en "Delete"
if (isset($_GET['delete'])) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'orchard_products';
    $product_id = intval($_GET['delete']);

    $wpdb->delete($table_name, ['product_id' => $product_id]);

    echo '<div class="updated"><p>Product deleted successfully!</p></div>';
}

// Marcar un producto como "Producto del Día"
if (isset($_GET['feature'])) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'orchard_products';
    $product_id = intval($_GET['feature']);

    // Primero, quitar la marca de todos los productos
    $wpdb->update($table_name, ['product_featured' => 0], ['product_featured' => 1]);

    // Ahora, marcar el producto seleccionado como "Producto del Día"
    $wpdb->update($table_name, ['product_featured' => 1], ['product_id' => $product_id]);

    echo '<div class="updated"><p>Product set as "Product of the Day" successfully!</p></div>';
}

// Función que renderiza la página
function orchard_products_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'orchard_products';

    // Verificar si se envió el formulario para agregar un producto
    if (isset($_POST['orchard_add_product'])) {
        $product_name = sanitize_text_field($_POST['product_name']);
        $product_description = sanitize_textarea_field($_POST['product_description']);
        $product_image = esc_url_raw($_POST['product_image']);

        $wpdb->insert($table_name, [
            'product_name' => $product_name,
            'product_description' => $product_description,
            'product_image' => $product_image
        ]);

        echo '<div class="updated"><p>Product added successfully!</p></div>';
    }

    // Obtener todos los productos de la base de datos
    $products = $wpdb->get_results("SELECT * FROM $table_name");

    ?>

    <div class="wrap">
        <h1>Manage Products</h1>

        <h2>Add New Product</h2>
        <form method="POST">
            <table class="form-table">
                <tr>
                    <th><label for="product_name">Product Name:</label></th>
                    <td><input type="text" name="product_name" required></td>
                </tr>
                <tr>
                    <th><label for="product_description">Product Description:</label></th>
                    <td><textarea name="product_description" required></textarea></td>
                </tr>
                <tr>
                    <th><label for="product_image">Product Image URL:</label></th>
                    <td><input type="text" name="product_image" required></td>
                </tr>
            </table>
            <input type="submit" name="orchard_add_product" class="button button-primary" value="Add Product">
        </form>

        <h2>Existing Products</h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Description</th>
                <th>Image</th>
                <th>Featured</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php if ($products) : ?>
                <?php foreach ($products as $product) : ?>
                    <tr>
                        <td><?php echo esc_html($product->product_id); ?></td>
                        <td><?php echo esc_html($product->product_name); ?></td>
                        <td><?php echo esc_html($product->product_description); ?></td>
                        <td><img src="<?php echo esc_url($product->product_image); ?>" width="50"></td>
                        <td>
                            <?php if ($product->product_featured == 1) : ?>
                                <strong style="color: green;">✔ Featured</strong>
                            <?php else : ?>
                                <a href="?page=orchard-products&feature=<?php echo esc_attr($product->product_id); ?>" class="button">Set as Product of the Day</a>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="?page=orchard-products&delete=<?php echo esc_attr($product->product_id); ?>" class="button button-danger">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="5">No products found.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php
}
