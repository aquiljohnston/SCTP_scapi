CREATE TABLE [dbo].[xxx_r_Reports_Old] (
    [rReportsID]         INT            NOT NULL,
    [ReportDisplayName]  VARCHAR (50)   NULL,
    [ReportDate]         DATE           NULL,
    [ReportInactiveDate] DATE           NULL,
    [ReportType]         VARCHAR (25)   NULL,
    [ReportSPName]       VARCHAR (100)  NULL,
    [ReportDescription]  VARCHAR (1000) NULL,
    [Parm]               VARCHAR (200)  NULL,
    [ParmInspector]      BIT            NULL,
    [ParmDropDown]       BIT            NULL,
    [ParmDateOverride]   BIT            NULL,
    [ParmBetweenDate]    BIT            NULL,
    [ParmDate]           BIT            NULL,
    [Export]             BIT            NULL,
    [RoleLevel]          INT            NULL,
    [Revision]           INT            NULL,
    [ActiveFlag]         BIT            NULL
);

