

CREATE View [dbo].[vDropDowns]
AS
select *
from [dbo].[rDropDown]
Where StartDate <= Cast(GetDate() As Date)
And ISNULL(InactiveDate, DateAdd(Day, 7, Getdate())) >= Cast(GetDate() As Date)
AND ActiveFlag = 1