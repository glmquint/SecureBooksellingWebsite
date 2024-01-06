function submitForm(value,type) {
    const formData = new FormData();
    formData.append('id', value);
    const addOrRemove = (type==='add') ? 'addtocart.php' : 'removefromcart.php';
    fetch(addOrRemove, {
            method: 'POST',
            body: formData
    })
        .then(response => {
            // Handle response if needed
        })
        .catch(error => {
            console.log(error)
        });
}