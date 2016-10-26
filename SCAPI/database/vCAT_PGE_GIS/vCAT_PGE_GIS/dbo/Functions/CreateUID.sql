

CREATE FUNCTION [dbo].[CreateUID]
(
	
	 @Type varchar(25)
	,@UniqueID varchar(50)
	,@SourceID varchar(25)
	,@CurrertDateTime datetime
	

)
RETURNS varchar(200)
AS
BEGIN
	
	DECLARE @ResultVar varchar(200)
		,@Seperator char(1)
		,@maxRandomValue int = 10
		,@minRandomValue int = 0
		,@NewUnipueID varchar(50)
		,@RandomNum Decimal(18,18)

	Select @RandomNum = RandomNumber From v_GetRandom

	Set @Seperator = '_'

	Select @NewUnipueID = REPLACE(Cast(Cast(((@maxRandomValue + 1) - @minRandomValue) 
	* @RandomNum + @minRandomValue As decimal(7,4)) * Cast(@UniqueID as int) as varchar(20)), '.', '')

	
	SELECT @ResultVar = @Type + '_' 
		+ Ltrim(Rtrim(@NewUnipueID)) 
		+ @Seperator
		+ Cast(DatePart(year, @CurrertDateTime) as char(4)) 
		+ Right('00' + Ltrim(rtrim(Cast(datepart(Month, @CurrertDateTime) as Char(2)))),2)
		+ Right('00' + Ltrim(rtrim(Cast(datepart(Day, @CurrertDateTime) as Char(2)))),2)
		+ Right('00' + Ltrim(rtrim(Cast(datepart(Hour, @CurrertDateTime) as Char(2)))),2)
		+ Right('00' + Ltrim(rtrim(Cast(datepart(Minute, @CurrertDateTime) as Char(2)))),2)
		+ Right('00' + Ltrim(rtrim(Cast(datepart(Second, @CurrertDateTime) as Char(2)))),2)
		+ @Seperator
		+ LTrim(RTrim(@SourceID))

	-- Return the result of the function
	RETURN @ResultVar

END
