

CREATE FUNCTION [dbo].[fnGetDistance] 
( 
       @Lat1		Decimal(18, 9)  
      ,@Long1		Decimal(18, 9)
      ,@Lat2		Decimal(18, 9) 
      ,@Long2		Decimal(18, 9) 
      ,@ReturnType	VARCHAR(10) 
)
--RETURNS FLOAT(18) AS
RETURNS DECIMAL(20,3) AS


/*****************************************************************************************************************
	NAME:		[fnGetDistance]
	AUTHOR:		Chris Bowker
	CREATED:	20140225

	**** REVISION HISTORY ***
	Developer		Date		    Description
	Chris Bowker	20140225		Initial Build


	**** TEST BLOCK ***

	DECLARE @Lat1			FLOAT(18)
	DECLARE @Long1			FLOAT(18)
	DECLARE @Lat2			FLOAT(18) 
	DECLARE @Long2			FLOAT(18)  
	DECLARE @ReturnType		VARCHAR(10)  -- ('Miles', 'Kilometers', 'Feet', 'Meters')


	SET @Lat1       = 37.77645			-- SCTP - San Ramon
	SET @Long1      = -121.9725
	SET @Lat2       = 37.78023			-- PG&E San Ramon Training Center
	SET @Long2      = -121.967409
	SET @ReturnType = 'Feet'

	SELECT [dbo].[fnGetDistance](@Lat1,@Long1,@Lat2,@Long2,@ReturnType)

******************************************************************************************************************/

BEGIN
    DECLARE 
	 @R Decimal(18, 9)
    ,@dLat Float(18)
    ,@dLon Float(18) 
    ,@a Float(18)
    ,@c Float(18) 
    ,@d Float(18)
	,@StartPoint geography
	,@EndPoint geography
	,@Distance Decimal(18, 9)
	
	SET @StartPoint = geography::Point(@Lat1, @Long1, 4326)
	SET @EndPoint= geography::Point(@Lat2, @Long2, 4326)

	Set @Distance = @StartPoint.STDistance(@EndPoint)

    SET @R =  
        CASE @ReturnType  
			WHEN 'Miles' THEN .000621371  
			WHEN 'Kilometers' THEN .001
			WHEN 'Feet' THEN 3.28084
			WHEN 'Meters' THEN 1
		ELSE 3.28084 -- Default feet (Garmin rel elev) 
        END

    
	/*SET @dLat	= RADIANS(@lat2 - @lat1)
    SET @dLon	= RADIANS(@long2 - @long1)
    SET @a		=   SIN(@dLat / 2)  
				  * SIN(@dLat / 2)  
		          + COS(RADIANS(@lat1)) 
			      * COS(RADIANS(@lat2))  
				  * SIN(@dLon / 2)  
				  * SIN(@dLon / 2)
    SET @c		= 2 * ASIN(MIN(SQRT(@a)))
	SET @d		= @R * @c
	*/
	
	RETURN Cast(@Distance * @R as Decimal(18, 3))

END




