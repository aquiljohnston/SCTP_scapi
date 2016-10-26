





CREATE FUNCTION [dbo].[fnWebManagementAvailableUserByIR](@IRUID varchar(100) )


RETURNS @TempUsers TABLE
(
	UserUID varchar(100)
	, UserFirstName  varchar(50)
	, UserLastName varchar(50)
	, Surveyor varchar(50)
)

AS
BEGIN

/*

Select * From fnTabletIR('User_57590026_20160822135947_Postman') Order by SortOrder, WorkCenter

*/

--Declare @UserUID varchar(100) = 'User_57590026_20160822135947_Postman'


	Insert Into @TempUsers
	select UserUID, UserFirstName, UserLastName, UserLastName + ', ' + UserFirstName [Surveyor] 
	from [dbo].[UserTb] u
	Left Join (Select AssignedUserUID from  [dbo].[tAssignedWorkQueue] Where AssignedInspectionRequestUID = @IRUID) awq on u.UserUID = awq.AssignedUserUID
	where awq.AssignedUserUID is null

	RETURN

END





