export const Selectors_LogoutPage = {


    /** @Here Admin is doing a Basic Logout and Validating the logout success
     * 
     * 
     * 
    */
    basicLogout:{
        logoutHoverUsername: '//a[@class="ab-item" and contains(text(), "Howdy, ")]',
        logoutButton: '//a[@class="ab-item" and contains(text(), "Log Out")]',

        //Validate LOGOUT
        logoutSucces: '//div[@class="wpuf-message"]',
       
    },

    validateBasicLogout: {
        
    },




};