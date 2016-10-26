
Create View [dbo].[vWebManagementDropDownDispatchStatus]
AS
select Distinct ir.StatusType [Status]
from tInspectionRequest ir where ir.StatusType <> 'Completed'
