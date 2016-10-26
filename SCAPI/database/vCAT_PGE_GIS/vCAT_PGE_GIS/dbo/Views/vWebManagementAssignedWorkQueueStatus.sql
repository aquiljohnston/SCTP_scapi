Create View vWebManagementAssignedWorkQueueStatus
AS

Select Distinct
CASE WHEN awq.LockedFlag = 0 THEN 'Assigned' ELSE 'In Progress' END [Status]
from
(SELECT * From [dbo].[tAssignedWorkQueue] where ActiveFlag = 1) awq