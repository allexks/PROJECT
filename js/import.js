function AutoSubmit() {
	val = document.getElementById("file-upload").value;

	if (val) {
		document.SubmitForm.submit();
	}
}