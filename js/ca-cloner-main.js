//from https://stackoverflow.com/questions/44933411/allow-only-letters-numbers-and-hyphen-in-input 
if (document.querySelector('#input_1_3')) {
  createUrlDiv();
  
  const input = document.querySelector('#input_1_3');
  const forbiddenChars = /[^a-z\d\-]/ig;

  input.addEventListener("change", function(e) {
    if (forbiddenChars.test(input.value)) {
      alert('Your site url had forbidden characters. Please only use lowercase letters, numbers or hyphens.');
      input.value = input.value.replace(forbiddenChars, '');
      return false;
    }    
    textSuccess();
  });

  input.addEventListener("blur", textSuccess);
  input.addEventListener("input", textSuccess); //react while typing
}

async function testSiteUrl(name) {
  const baseUrl = window.location.origin;
  const apiUrl = `/wp-json/multisite/v1/check/${name}`;
  const url = baseUrl + apiUrl;
  
  try {
    const response = await fetch(url);
    if (!response.ok) {
      throw new Error(`Response status: ${response.status}`);
    }
    const json = await response.json();
    return json.exists;    
  } catch (error) {
    console.error(error.message);
    return null;
  }
}


//makes the div that holds the URL example
function createUrlDiv() {
  let fieldId = document.getElementById('field_1_3');
  
  // Create domain div
  let domain = document.createElement("div");
  fieldId.appendChild(domain);
  const baseDomain = window.location.origin;
  domain.setAttribute("id", "domain-name");
  domain.innerHTML = `${baseDomain}/YOUR_DOMAIN_NAME`;
  
  // Create text-ok div
  const textOk = document.createElement("div");
  textOk.setAttribute("id", "text-ok");
  fieldId.appendChild(textOk);
  
  // Create site-ok div
  const siteOk = document.createElement("div");
  siteOk.setAttribute("id", "site-ok");
  fieldId.appendChild(siteOk);
}

//makes the URL go green when all is ok
async function textSuccess() {
  const input = document.querySelector('#input_1_3');
  const domainName = document.querySelector('#domain-name');//The URL
  const textOk = document.querySelector('#text-ok')
  const siteOk = document.querySelector('#site-ok')
  const forbiddenChars = /[^a-z\d\-]/ig;
  
  // Get site existence status (await the Promise)
  const exists = await testSiteUrl(input.value);
  
  const theBaseDomain = window.location.origin;
  document.getElementById('domain-name').innerHTML = `${theBaseDomain}/${input.value}`;
  
  if(exists === false){
    siteOk.setAttribute("class","site-ok");
  } 
   if(exists === true){
    siteOk.setAttribute("class","not-ok");
  } 

  if (input.value && !forbiddenChars.test(input.value) && exists === false) {
    // No forbidden characters and site doesn't exist — set success
    domainName.setAttribute("class", "success");    
  } else {
    // Bad or empty value — remove success   
  }
}


//MODAL STUFF FOR CLONES
function modalButton(){
  if(document.querySelector("#modal-button")){
    const dialog = document.querySelector("#clone-modal");
    const showButton = document.querySelector("#modal-button");
    const closeButton = document.querySelector("#clone-modal #close");
    const entryName = document.querySelector("#field_1_2");
    // "Show the dialog" button opens the dialog modally
    showButton.addEventListener("click", () => {
      //dialog.showModal();
      dialog.classList.remove('hidden');
      if (entryName) {
        entryName.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });

    // "Close" button closes the dialog
    closeButton.addEventListener("click", () => {
      //dialog.close();
      dialog.classList.add('hidden');
    });
  }
}

 modalButton();


if (document.querySelector("#gform_submit_button_1")){
   const submitButton = document.querySelector('#gform_submit_button_1');
    
    if (submitButton) {
      // Add click event listener to the submit button
      submitButton.addEventListener('click', event => {
        // Your custom JavaScript function
        const processAlert = document.querySelector("#in-process")
        processAlert.showModal();

        
        // If you want the form to still submit normally, don't prevent default
        // If you want to handle submission yourself: event.preventDefault();
      });
  }
}