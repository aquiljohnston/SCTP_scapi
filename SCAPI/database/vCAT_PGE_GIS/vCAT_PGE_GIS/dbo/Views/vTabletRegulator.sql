
/****** Script for SelectTopNRows command from SSMS  ******/
CREATE View [dbo].[vTabletRegulator]
AS

SELECT Distinct ISNULL(RegulatorSizeType, 'N/A') [RegulatorSizeType], RegulatorMfgType, RegulatorModelType
FROM [dbo].[rRegulator]
Where ActiveFlag = 1 and StatusType = 'Active'


--select * from [dbo].[rRegulator]
