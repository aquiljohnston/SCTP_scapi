
/****** Script for SelectTopNRows command from SSMS  ******/
CREATE View [dbo].[vTabletMeter]
AS

SELECT Distinct MeterType, MeterMfgType, MeterModelType
FROM [dbo].[rMeter]
Where ActiveFlag = 1 and StatusType = 'Active'


--select * from [dbo].[rMeter]
