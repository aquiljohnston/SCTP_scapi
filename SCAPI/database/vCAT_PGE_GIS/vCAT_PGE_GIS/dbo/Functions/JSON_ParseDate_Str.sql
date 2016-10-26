




CREATE FUNCTION [dbo].[JSON_ParseDate_Str]
(
	-- Add the parameters for the function here
	@myDate varchar(50)
	
)
RETURNS varchar(50)
AS
BEGIN
	-- Declare the return variable here
	DECLARE @ResultVar varchar(50)
-- Select [dbo].[JSON_ParseDate_Str]('2014-07-22 13:48:07')
	
	set @myDate = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(@myDate, '-', ''),'/',''),':',''), ' ',''), '\', '')

	--Select @ResultVar = Left(@myDate, 4) + '-' + SUBSTRING(@myDate,5,2) + '-' + SUBSTRING(@myDate,7,2) + ' ' + SUBSTRING(@myDate,9,2) + ':' + SUBSTRING(@myDate,11,2) + ':' + SUBSTRING(@myDate,13,2)

	Select @ResultVar = SUBSTRING(@myDate,5,2) + '/' + SUBSTRING(@myDate,7,2) + '/' +  Left(@myDate, 4) + ' ' + SUBSTRING(@myDate,9,2) + ':' + SUBSTRING(@myDate,11,2) + ':' + SUBSTRING(@myDate,13,2)

	
	-- Return the result of the function
	RETURN @ResultVar

END






