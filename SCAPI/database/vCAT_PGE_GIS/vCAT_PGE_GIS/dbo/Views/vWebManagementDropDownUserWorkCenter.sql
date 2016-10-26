


CREATE View [dbo].[vWebManagementDropDownUserWorkCenter]
AS
SELECT Distinct
WorkCenter, WorkCenterUID
FROM rWorkCenter
Where ActiveFlag = 1

