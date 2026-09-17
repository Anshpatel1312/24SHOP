<?php
// MODAL: ADD / EDIT PRODUCT
?>
<div class="modal-overlay" id="productModal">
    <div class="modal-box">
        <div class="modal-header">
            <h3 id="modalTitle">Add New Product</h3>
            <button class="modal-close" onclick="closeProductModal()"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <form action="admin.php" method="POST" enctype="multipart/form-data" id="productForm">
            <input type="hidden" name="action" id="formAction" value="add_product">
            <input type="hidden" name="product_id" id="formProductId" value="">

            <div class="form-grid">
                <div class="form-group full">
                    <label>Product Name</label>
                    <input type="text" name="product_name" id="inpName" required placeholder="e.g. Slim Fit Cotton Polo Shirt">
                </div>

                <div class="form-group">
                    <label>Category</label>
                    <input type="text" name="category" id="inpCat" required placeholder="e.g. Men's Fashion, Electronics">
                </div>

                <div class="form-group">
                    <label>Brand</label>
                    <input type="text" name="brand" id="inpBrand" required placeholder="e.g. 24SHOP Exclusive">
                </div>

                <div class="form-group">
                    <label>Gender / Target</label>
                    <select name="gender" id="inpGender">
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="boy">Boy</option>
                        <option value="girl">Girl</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Stock Quantity</label>
                    <input type="number" name="stockqty" id="inpStock" min="0" required placeholder="10">
                </div>

                <div class="form-group">
                    <label>Selling Price (₹)</label>
                    <input type="number" step="0.01" name="price" id="inpPrice" required placeholder="1299.00">
                </div>

                <div class="form-group">
                    <label>Discount / Original MRP (₹)</label>
                    <input type="number" step="0.01" name="discountprice" id="inpDiscount" placeholder="1799.00">
                </div>

                <div class="form-group">
                    <label>Available Color(s)</label>
                    <input type="text" name="color" id="inpColor" placeholder="e.g. Navy Blue, Black">
                </div>

                <div class="form-group">
                    <label>Available Size(s)</label>
                    <input type="text" name="size" id="inpSize" placeholder="e.g. S, M, L, XL">
                </div>

                <div class="form-group full">
                    <label>Product Description</label>
                    <textarea name="description" id="inpDesc" rows="3" required placeholder="Detailed description..."></textarea>
                </div>

                <div class="form-group full">
                    <label>Product Image</label>
                    <input type="file" name="image" id="inpImage" accept="image/*">
                </div>
            </div>

            <div class="form-actions">
                <button type="button" class="btn-cancel" onclick="closeProductModal()">Cancel</button>
                <button type="submit" class="btn-primary" id="btnSubmitForm">Save Product</button>
            </div>
        </form>
    </div>
</div>
