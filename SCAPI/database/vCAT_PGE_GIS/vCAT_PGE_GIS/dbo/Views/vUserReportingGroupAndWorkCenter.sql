


Create view [dbo].[vUserReportingGroupAndWorkCenter]
AS
select 
u.UserLastName, u.UserFirstName, u.UserName, rg.GroupName, wc.Region, wc.WorkCenter 
from UserTb u
left join [dbo].[xReportingGroupEmployeexRef] rge on rge.UserUID = u.UserUID
left join [dbo].[rReportingGroup] rg on rg.ReportingGroupUID = rge.ReportingGroupUID
Left Join [dbo].[xReportingGroupAndWorkcenterxRef] rgwc on rgwc.ReportingGroupUID = rg.ReportingGroupUID
Left Join [dbo].[rWorkCenter] wc on rgwc.WorkCenterUID = wc.WorkCenterUID
--where u.UserName = 'joey'