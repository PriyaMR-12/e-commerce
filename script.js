// script.js
let cart = [];

function addToCart(productId) {
    // Example products data (you can fetch this from your database)
    const products = [
        { id: 1, name: "Product 1", price: 10.00 },
        { id: 2, name: "Product 2", price: 20.00 }
    ];

    const product = products.find(p => p.id === productId);
    if (product) {
        cart.push(product);
        alert(`${product.name} has been added to your cart!`);
    }
}

// You can later create functions to display the cart or proceed to checkout
