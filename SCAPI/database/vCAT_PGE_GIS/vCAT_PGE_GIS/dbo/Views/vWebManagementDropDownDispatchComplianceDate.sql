
CREATE View [dbo].[vWebManagementDropDownDispatchComplianceDate]
AS

select Distinct Cast(Year(ir.ComplianceDueDate) as Char(4)) + ' - ' + datename(mm, ir.ComplianceDueDate) As ComplianceYearMonth
, Cast(Year(ir.ComplianceDueDate) as Char(4)) + Right('00' + Cast(Month(ir.ComplianceDueDate) as varchar(2)), 2) AS ComplianceSort
from tInspectionRequest ir
Left Join (Select * from [dbo].[tAssignedWorkQueue] where ActiveFlag = 1) awq on awq.AssignedInspectionRequestUID = ir.InspectionRequestUID
WHERE ir.StatusType <> 'Completed'  and awq.AssignedInspectionRequestUID is null
