class Http{
    constructor(url) {
        this.baseUrl = url ? url : Base.siteUrl;
    }
    async get(url){
       const response = await fetch(this.baseUrl + url, {
           credentials: 'include',
           headers:{
               'X-Requested-With':'xmlhttprequest'
           }
       });
       return await response.json();
           
    }
    async post(url,data){
      const response = await fetch(this.baseUrl + url,{
               method:'POST',
               credentials: 'include',
               headers:{
                   "Content-Type":'application/json',
                   'X-Requested-With':'xmlhttprequest'
               },
               body:JSON.stringify(data)
           })
         
           return await response.json();
       
    }
    async put(url,data){
       const response = await fetch(this.baseUrl + url,{
               method:'PUT',
               credentials: 'include',
               headers:{
                   "Content-Type":'application/json',
                   'X-Requested-With':'xmlhttprequest'
               },
               body:JSON.stringify(data)
           })
           return await response.json();
      
    }

    async delete(url){
       const response = await fetch(this.baseUrl + url,{
              method:'DELETE',
              credentials: 'include',
              headers:{
                  "Content-Type":'application/json',
                  'X-Requested-With':'xmlhttprequest'
              }
          })
      
          return await "删除成功";
      
          
   }

}
export default Http