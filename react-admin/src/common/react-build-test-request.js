const http = new Http();

const ReactBuildTestRequest = {
    getTest: stageId => http.get(ApiUrl.reactBuildTestGet+'?stage_id='+stageId),
    createTest: data => http.post(ApiUrl.reactBuildTestPost, data),
    updateTest: data => http.put(ApiUrl.reactBuildTestPut,data),
    deleteTest: id => http.delete(ApiUrl.reactBuildTestDelete+'?id='+id),
}

export default ReactBuildTestRequest;